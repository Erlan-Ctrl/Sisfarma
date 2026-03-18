<?php

namespace App\Services\Ai;

use App\Models\AiConversation;
use App\Models\AiKnowledgeEntry;
use App\Models\AiMessage;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiAssistantService
{
    private function providerCooldownKey(string $provider): string
    {
        return 'ai:provider_cooldown_until:'.$provider;
    }

    private function providerCooldownRemaining(string $provider): int
    {
        $until = (int) Cache::get($this->providerCooldownKey($provider), 0);
        $remaining = $until - time();

        return $remaining > 0 ? $remaining : 0;
    }

    private function setProviderCooldown(string $provider, int $seconds): void
    {
        $seconds = max(1, $seconds);
        Cache::put($this->providerCooldownKey($provider), time() + $seconds, $seconds);
    }

    private function isTransientProviderFailure(\Throwable $e): bool
    {
        if ($e instanceof ConnectionException) {
            return true;
        }

        $m = strtolower($e->getMessage());
        return str_contains($m, 'failed to connect')
            || str_contains($m, 'could not connect')
            || str_contains($m, 'connection refused')
            || str_contains($m, 'curl error 7')
            || str_contains($m, 'timed out')
            || str_contains($m, 'timeout');
    }

    private function publicProviderError(string $provider, \Throwable $e): string
    {
        $msg = strtolower($e->getMessage());

        if ($provider === 'ollama') {
            if ($this->isTransientProviderFailure($e)) {
                return 'Ollama indisponível (não consegui conectar). Abra o Ollama ou rode `ollama serve` e verifique AI_OLLAMA_URL.';
            }

            if (str_contains($msg, 'model') && (str_contains($msg, 'not found') || str_contains($msg, 'no such') || str_contains($msg, 'unknown'))) {
                $model = (string) config('ai.ollama.model', '');
                return $model !== ''
                    ? "Modelo do Ollama não encontrado ({$model}). Rode: `ollama pull {$model}`."
                    : 'Modelo do Ollama não encontrado. Verifique AI_OLLAMA_MODEL.';
            }

            return 'Falha ao chamar o Ollama; usando resposta local.';
        }

        if ($provider === 'openai') {
            if (str_contains($msg, 'curl error 60') || str_contains($msg, 'ssl certificate')) {
                return 'Falha SSL ao chamar OpenAI; usando resposta local.';
            }

            if (str_contains($msg, 'insufficient_quota') || str_contains($msg, 'exceeded your current quota') || str_contains($msg, 'quota')) {
                return 'OpenAI sem quota/crédito para a API; usando resposta local.';
            }

            if ($this->isTransientProviderFailure($e)) {
                return 'OpenAI indisponível (falha de conexão/timeout); usando resposta local.';
            }

            return 'Falha ao chamar a OpenAI; usando resposta local.';
        }

        return 'Falha ao chamar o provedor de IA; usando resposta local.';
    }

    /**
     * @return array{content: string, meta: array<string, mixed>}
     */
    public function reply(AiConversation $conversation, string $userText): array
    {
        $text = trim($userText);
        $context = $this->retrieveContext($text);

        $provider = (string) config('ai.provider', 'local');
        $provider = $provider !== '' ? $provider : 'local';

        $result = null;

        if ($provider === 'openai') {
            if (filled((string) config('ai.openai.api_key'))) {
                $cooldown = $this->providerCooldownRemaining('openai');
                if ($cooldown > 0) {
                    $result = $this->replyLocal($text, $context);
                    $result['meta']['provider_error'] = "OpenAI indisponível no momento. Tentarei novamente em {$cooldown}s.";
                } else {
                try {
                    $result = $this->replyWithOpenAi($conversation, $text, $context);
                } catch (\Throwable $e) {
                    $providerError = $this->publicProviderError('openai', $e);
                    $logError = Str::limit($e->getMessage(), 1200);

                    Log::warning('OpenAI provider failed; using local fallback.', [
                        'conversation_id' => $conversation->getKey(),
                        'exception' => get_class($e),
                        'error' => $logError,
                    ]);

                    if ($this->isTransientProviderFailure($e)) {
                        $this->setProviderCooldown('openai', (int) config('ai.cooldown_seconds', 30));
                    }

                    $result = $this->replyLocal($text, $context);
                    $result['meta']['provider_error'] = $providerError;
                }
                }
            } else {
                $result = $this->replyLocal($text, $context);
                $result['meta']['provider_error'] = 'OPENAI_API_KEY não configurada.';
            }
        } elseif ($provider === 'ollama') {
            $url = trim((string) config('ai.ollama.url', ''));
            $model = trim((string) config('ai.ollama.model', ''));

            if ($url !== '' && $model !== '') {
                $cooldown = $this->providerCooldownRemaining('ollama');
                if ($cooldown > 0) {
                    $result = $this->replyLocal($text, $context);
                    $result['meta']['provider_error'] = "Ollama indisponível no momento. Tentarei novamente em {$cooldown}s.";
                } else {
                try {
                    $result = $this->replyWithOllama($conversation, $text, $context);
                } catch (\Throwable $e) {
                    $providerError = $this->publicProviderError('ollama', $e);
                    $logError = Str::limit($e->getMessage(), 1200);

                    Log::warning('Ollama provider failed; using local fallback.', [
                        'conversation_id' => $conversation->getKey(),
                        'exception' => get_class($e),
                        'error' => $logError,
                    ]);

                    if ($this->isTransientProviderFailure($e)) {
                        $this->setProviderCooldown('ollama', (int) config('ai.cooldown_seconds', 30));
                    }

                    $result = $this->replyLocal($text, $context);
                    $result['meta']['provider_error'] = $providerError;
                }
                }
            } else {
                $result = $this->replyLocal($text, $context);
                $result['meta']['provider_error'] = 'AI_OLLAMA_URL/AI_OLLAMA_MODEL não configurado.';
            }
        }

        if (! $result) {
            $result = $this->replyLocal($text, $context);
        }

        $result['meta']['provider'] = $result['meta']['provider'] ?? $provider;

        // Auto-learn: salva rascunhos de conhecimento quando fizer sentido.
        if ((bool) config('ai.auto_learn.enabled', true)) {
            $learnedIds = $this->autoLearn(
                $conversation,
                $text,
                $result['content'],
                (array) ($result['meta']['learned_suggestions'] ?? []),
                (string) ($result['meta']['provider'] ?? $provider)
            );

            if ($learnedIds !== []) {
                $result['meta']['learned_entry_ids'] = $learnedIds;
            }
        }

        unset($result['meta']['learned_suggestions']);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function retrieveContext(string $text): array
    {
        $digits = preg_replace('/\\D+/', '', $text);
        $keywords = $this->keywords($text);

        $knowledge = AiKnowledgeEntry::query()
            ->where(function ($query) {
                $query->where('is_active', true)->orWhere('source_type', 'conversation');
            })
            ->when($text !== '', function ($query) use ($text, $keywords) {
                $query->where(function ($sub) use ($text, $keywords) {
                    $sub
                        ->where('title', 'ilike', "%{$text}%")
                        ->orWhere('content', 'ilike', "%{$text}%")
                        ->orWhere('tags', 'ilike', "%{$text}%");

                    foreach ($keywords as $kw) {
                        $sub->orWhere('title', 'ilike', "%{$kw}%")
                            ->orWhere('content', 'ilike', "%{$kw}%");
                    }
                });
            })
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $memory = AiMessage::query()
            ->where('role', 'assistant')
            ->when($text !== '', function ($query) use ($text, $keywords) {
                $query->where(function ($sub) use ($text, $keywords) {
                    $sub->where('content', 'ilike', "%{$text}%");
                    foreach ($keywords as $kw) {
                        $sub->orWhere('content', 'ilike', "%{$kw}%");
                    }
                });
            })
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->when($digits !== '' && strlen($digits) >= 8, function ($query) use ($digits) {
                $query->where('ean', $digits);
            }, function ($query) use ($text) {
                if ($text === '') {
                    return;
                }

                $query->where(function ($sub) use ($text) {
                    $sub
                        ->where('name', 'ilike', "%{$text}%")
                        ->orWhere('sku', 'ilike', "%{$text}%")
                        ->orWhere('ean', 'ilike', "%{$text}%");
                });
            })
            ->orderBy('name')
            ->limit(6)
            ->get();

        $offers = Offer::query()
            ->where('is_active', true)
            ->when($text !== '', function ($query) use ($text) {
                $query->where(function ($sub) use ($text) {
                    $sub
                        ->where('title', 'ilike', "%{$text}%")
                        ->orWhere('description', 'ilike', "%{$text}%");
                });
            })
            ->orderByDesc('starts_at')
            ->limit(5)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->when($text !== '', function ($query) use ($text) {
                $query->where('name', 'ilike', "%{$text}%");
            })
            ->orderBy('name')
            ->limit(4)
            ->get();

        $stores = Store::query()
            ->where('is_active', true)
            ->when($text !== '', function ($query) use ($text) {
                $query->where(function ($sub) use ($text) {
                    $sub->where('name', 'ilike', "%{$text}%")->orWhere('city', 'ilike', "%{$text}%");
                });
            })
            ->orderBy('name')
            ->limit(4)
            ->get();

        return [
            'text' => $text,
            'digits' => $digits,
            'keywords' => $keywords,
            'knowledge' => $knowledge,
            'memory' => $memory,
            'products' => $products,
            'offers' => $offers,
            'categories' => $categories,
            'stores' => $stores,
            'sources' => $this->buildSources($knowledge, $products, $offers, $categories, $stores),
        ];
    }

    /**
     * @return array<int, array{type: string, id: int, label: string, url: string}>
     */
    private function buildSources($knowledge, $products, $offers, $categories, $stores): array
    {
        $sources = [];

        foreach ($knowledge as $entry) {
            $status = $entry->is_active ? 'Conhecimento' : 'Conhecimento (rascunho)';
            $sources[] = [
                'type' => 'knowledge',
                'id' => (int) $entry->getKey(),
                'label' => "{$status}: {$entry->title}",
                'url' => route('admin.knowledge.show', $entry),
            ];
        }

        foreach ($products as $product) {
            $sources[] = [
                'type' => 'product',
                'id' => (int) $product->getKey(),
                'label' => "Produto: {$product->name}",
                'url' => route('admin.products.show', $product),
            ];
        }

        foreach ($offers as $offer) {
            $sources[] = [
                'type' => 'offer',
                'id' => (int) $offer->getKey(),
                'label' => "Oferta: {$offer->title}",
                'url' => route('admin.offers.show', $offer),
            ];
        }

        foreach ($categories as $category) {
            $sources[] = [
                'type' => 'category',
                'id' => (int) $category->getKey(),
                'label' => "Categoria: {$category->name}",
                'url' => route('admin.categories.show', $category),
            ];
        }

        foreach ($stores as $store) {
            $sources[] = [
                'type' => 'store',
                'id' => (int) $store->getKey(),
                'label' => "Loja: {$store->name}",
                'url' => route('admin.stores.show', $store),
            ];
        }

        return $sources;
    }

    /**
     * @return array{content: string, meta: array<string, mixed>}
     */
    private function replyLocal(string $text, array $context): array
    {
        $digits = (string) ($context['digits'] ?? '');

        $meta = [
            'provider' => 'local',
            'mode' => $digits !== '' ? 'barcode_or_search' : 'search',
            'sources' => $context['sources'] ?? [],
        ];

        if ($digits !== '' && strlen($digits) >= 8 && $digits === $text) {
            $product = Product::query()->where('ean', $digits)->first();
            if ($product) {
                $content = implode("\n", [
                    "Encontrei este produto pelo EAN:",
                    "- Nome: {$product->name}",
                    "- EAN: {$product->ean}",
                    "- SKU: ".($product->sku ?: '-'),
                    "- Abrir: ".route('admin.products.show', $product),
                    "- Editar: ".route('admin.products.edit', $product),
                ]);

                return ['content' => $content, 'meta' => $meta];
            }

            $content = implode("\n", [
                "Não encontrei nenhum produto com EAN {$digits}.",
                'Sugestão:',
                "- Scanner: ".route('admin.scanner', ['code' => $digits]),
                "- Criar produto: ".route('admin.products.create', ['ean' => $digits]),
            ]);

            return ['content' => $content, 'meta' => $meta];
        }

        $knowledge = $context['knowledge'] ?? collect();
        $products = $context['products'] ?? collect();
        $offers = $context['offers'] ?? collect();
        $categories = $context['categories'] ?? collect();
        $stores = $context['stores'] ?? collect();

        $lines = [];
        $lines[] = 'Posso te ajudar com base no que existe no sistema. Aqui vai o que encontrei:';

        if ($knowledge->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Conhecimento (procedimentos):';
            foreach ($knowledge as $entry) {
                $lines[] = "- {$entry->title} (ver: ".route('admin.knowledge.show', $entry).')';
            }
        }

        if ($products->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Produtos:';
            foreach ($products as $product) {
                $lines[] = "- {$product->name} (ver: ".route('admin.products.show', $product).')';
            }
        }

        if ($offers->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Ofertas:';
            foreach ($offers as $offer) {
                $lines[] = "- {$offer->title} (ver: ".route('admin.offers.show', $offer).')';
            }
        }

        if ($categories->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Categorias:';
            foreach ($categories as $category) {
                $lines[] = "- {$category->name} (ver: ".route('admin.categories.show', $category).')';
            }
        }

        if ($stores->isNotEmpty()) {
            $lines[] = '';
            $lines[] = 'Lojas:';
            foreach ($stores as $store) {
                $lines[] = "- {$store->name} (ver: ".route('admin.stores.show', $store).')';
            }
        }

        if ($knowledge->isEmpty() && $products->isEmpty() && $offers->isEmpty() && $categories->isEmpty() && $stores->isEmpty()) {
            $lines[] = '';
            $lines[] = 'Não encontrei nada no sistema com isso ainda.';
            $lines[] = 'Sugestão: tente pelo Scanner (EAN) ou cadastre/ajuste um produto.';
            $lines[] = '- Scanner: '.route('admin.scanner', ['code' => $text]);
            $lines[] = '- Produtos: '.route('admin.products.index', ['q' => $text]);
        }

        return [
            'content' => implode("\n", $lines),
            'meta' => $meta,
        ];
    }

    /**
     * @return array{content: string, meta: array<string, mixed>}
     */
    private function replyWithOpenAi(AiConversation $conversation, string $text, array $context): array
    {
        $apiKey = (string) config('ai.openai.api_key');
        $model = (string) config('ai.openai.model', 'gpt-4o-mini');
        $temperature = (float) config('ai.openai.temperature', 0.2);
        $timeout = (int) config('ai.openai.timeout', 30);
        $baseUrl = (string) config('ai.openai.base_url', 'https://api.openai.com');
        $verify = $this->openAiVerifyOption();

        $client = new OpenAiChatClient($apiKey, $model, $temperature, $timeout, $baseUrl, $verify);

        $history = $conversation->messages()
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->reverse()
            ->values();

        $system = $this->systemPrompt();
        $userPrompt = $this->userPrompt($text, $context);

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $system];

        // Keep short history to help the model keep context.
        foreach ($history as $msg) {
            if (! in_array($msg->role, ['user', 'assistant'], true)) {
                continue;
            }
            $messages[] = [
                'role' => $msg->role,
                'content' => Str::limit($this->redactSensitive((string) $msg->content), 1200),
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        $payload = $client->chatJson($messages);

        $answer = $this->stringifyAiContent($payload['answer'] ?? '');
        if ($answer === '') {
            $answer = 'Não consegui gerar uma resposta agora. Tente novamente.';
        }

        $learned = [];
        $suggestions = $payload['learned'] ?? [];
        if (is_array($suggestions)) {
            $learned = array_values(array_filter($suggestions, fn ($i) => is_array($i)));
        }

        $meta = [
            'provider' => 'openai',
            'model' => $model,
            'sources' => $context['sources'] ?? [],
            'learned_suggestions' => array_slice($learned, 0, (int) config('ai.auto_learn.max_suggestions', 3)),
        ];

        return [
            'content' => $answer,
            'meta' => $meta,
        ];
    }

    /**
     * @return array{content: string, meta: array<string, mixed>}
     */
    private function replyWithOllama(AiConversation $conversation, string $text, array $context): array
    {
        $url = (string) config('ai.ollama.url', 'http://127.0.0.1:11434');
        $model = (string) config('ai.ollama.model', 'llama3.1:8b');
        $temperature = (float) config('ai.ollama.temperature', 0.2);
        $timeout = (int) config('ai.ollama.timeout', 60);
        $connectTimeout = (float) config('ai.ollama.connect_timeout', 1.2);

        $keepAlive = config('ai.ollama.keep_alive');

        $options = array_filter([
            'num_ctx' => (int) config('ai.ollama.num_ctx', 0) ?: null,
            'num_predict' => (int) config('ai.ollama.num_predict', 0) ?: null,
            'top_k' => (int) config('ai.ollama.top_k', 0) ?: null,
            'top_p' => (float) config('ai.ollama.top_p', 0) ?: null,
            'repeat_penalty' => (float) config('ai.ollama.repeat_penalty', 0) ?: null,
            'seed' => config('ai.ollama.seed'),
            'num_thread' => config('ai.ollama.num_thread'),
        ], static fn ($v) => $v !== null && $v !== '');

        $client = new OllamaChatClient(
            $url,
            $model,
            $temperature,
            $timeout,
            $connectTimeout,
            $options,
            is_string($keepAlive) ? $keepAlive : null
        );

        $history = $conversation->messages()
            ->orderByDesc('id')
            ->limit(max(1, (int) config('ai.ollama.history_limit', 8)))
            ->get()
            ->reverse()
            ->values();

        $system = $this->systemPrompt();
        $userPrompt = $this->userPrompt($text, $context);

        $charLimit = max(200, (int) config('ai.ollama.message_char_limit', 800));

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $system];

        foreach ($history as $msg) {
            if (! in_array($msg->role, ['user', 'assistant'], true)) {
                continue;
            }
            $messages[] = [
                'role' => $msg->role,
                'content' => Str::limit($this->redactSensitive((string) $msg->content), $charLimit),
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $userPrompt];

        $payload = $client->chatJson($messages);

        $answer = $this->stringifyAiContent($payload['answer'] ?? '');
        if ($answer === '') {
            $answer = 'Não consegui gerar uma resposta agora. Tente novamente.';
        }

        $learned = [];
        $suggestions = $payload['learned'] ?? [];
        if (is_array($suggestions)) {
            $learned = array_values(array_filter($suggestions, fn ($i) => is_array($i)));
        }

        $meta = [
            'provider' => 'ollama',
            'model' => $model,
            'sources' => $context['sources'] ?? [],
            'learned_suggestions' => array_slice($learned, 0, (int) config('ai.auto_learn.max_suggestions', 3)),
        ];

        return [
            'content' => $answer,
            'meta' => $meta,
        ];
    }

    private function systemPrompt(): string
    {
        $maxLearn = (int) config('ai.auto_learn.max_suggestions', 3);

        return implode("\n", [
            'Você é um assistente interno da farmácia (painel administrativo).',
            'Foco: processos internos, cadastro/consulta no sistema e rotinas da equipe.',
            '',
            'Regras:',
            '- Use o CONTEXTO para dados do sistema (produtos, lojas, ofertas, procedimentos).',
            '- Não invente IDs, EAN, preços, estoque, endereços ou qualquer dado que pareça vir do sistema.',
            '- Se o CONTEXTO não trouxer a resposta, você pode sugerir um procedimento genérico (boas práticas), mas deixe claro que é um rascunho e precisa de validação interna.',
            '- Itens de conhecimento com ativo=não são rascunhos: use com cautela e sugira revisão.',
            '- Se não houver informação no contexto, diga que não encontrou no sistema e sugira o próximo passo.',
            '- Evite orientação médica/paciente-específica. Se a pergunta for clínica, responda com cautela e recomende seguir protocolos internos.',
            '- Não inclua nem salve dados pessoais (CPF, telefone, endereço de paciente) em "learned".',
            '',
            'Formato de saída OBRIGATÓRIO: JSON.',
            'Chaves:',
            '- answer: string (resposta em pt-BR, objetiva, com passos e links quando útil)',
            "- learned: array (até {$maxLearn} itens) de objetos com {title, content, tags}. Só inclua itens reutilizáveis (procedimentos/checklists).",
        ]);
    }

    private function userPrompt(string $text, array $context): string
    {
        $contextText = $this->contextToText($context);
        $text = $this->redactSensitive($text);

        return implode("\n", [
            "PERGUNTA DO USUARIO:\n{$text}",
            '',
            "CONTEXTO DO SISTEMA:\n".$this->redactSensitive($contextText),
            '',
            'Se fizer sentido, inclua links internos (ex.: /admin/produtos/ID, /admin/scanner?code=...).',
        ]);
    }

    private function contextToText(array $context): string
    {
        $chunks = [];

        $knowledge = $context['knowledge'] ?? collect();
        if ($knowledge->isNotEmpty()) {
            $chunks[] = 'Conhecimento:';
            foreach ($knowledge as $entry) {
                $chunks[] = "- [K{$entry->getKey()}] {$entry->title} | ativo=".($entry->is_active ? 'sim' : 'não').": ".Str::limit(preg_replace('/\\s+/', ' ', (string) $entry->content), 400);
            }
        }

        $products = $context['products'] ?? collect();
        if ($products->isNotEmpty()) {
            $chunks[] = 'Produtos:';
            foreach ($products as $p) {
                $chunks[] = "- [P{$p->getKey()}] {$p->name} | EAN={$p->ean} | SKU={$p->sku} | ativo=".($p->is_active ? 'sim' : 'não');
            }
        }

        $offers = $context['offers'] ?? collect();
        if ($offers->isNotEmpty()) {
            $chunks[] = 'Ofertas:';
            foreach ($offers as $o) {
                $chunks[] = "- [O{$o->getKey()}] {$o->title} | ativa=".($o->is_active ? 'sim' : 'não');
            }
        }

        $categories = $context['categories'] ?? collect();
        if ($categories->isNotEmpty()) {
            $chunks[] = 'Categorias:';
            foreach ($categories as $c) {
                $chunks[] = "- [C{$c->getKey()}] {$c->name}";
            }
        }

        $stores = $context['stores'] ?? collect();
        if ($stores->isNotEmpty()) {
            $chunks[] = 'Lojas:';
            foreach ($stores as $s) {
                $chunks[] = "- [S{$s->getKey()}] {$s->name} | {$s->city}/{$s->state} | tel={$s->phone}";
            }
        }

        $memory = $context['memory'] ?? collect();
        if ($memory->isNotEmpty()) {
            $chunks[] = 'Histórico (respostas anteriores):';
            foreach ($memory as $m) {
                $chunks[] = '- '.Str::limit(preg_replace('/\\s+/', ' ', (string) $m->content), 240);
            }
        }

        if ($chunks === []) {
            return 'Nenhum dado relevante encontrado.';
        }

        return implode("\n", $chunks);
    }

    /**
     * @return bool|string
     */
    private function openAiVerifyOption(): bool|string
    {
        $verifySsl = (bool) config('ai.openai.verify_ssl', true);
        if (! $verifySsl) {
            return false;
        }

        $caBundle = trim((string) config('ai.openai.ca_bundle', ''));
        if ($caBundle === '') {
            return true;
        }

        // Allow relative paths (project root).
        foreach ([$caBundle, base_path($caBundle)] as $path) {
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }

        return true;
    }

    private function redactSensitive(mixed $text): string
    {
        // Providers (and even local callers) may occasionally return arrays/objects.
        // Normalize early so we never trigger "Array to string conversion" notices.
        $out = $this->stringifyAiContent($text);

        // Emails
        $out = preg_replace('/\\b[\\w.\\-+]+@[\\w\\-]+\\.[\\w.\\-]+\\b/', '[email]', $out) ?? $out;

        // CPF (11 digits, with or without punctuation)
        $out = preg_replace('/\\b\\d{3}\\.?\\d{3}\\.?\\d{3}-?\\d{2}\\b/', '[cpf]', $out) ?? $out;

        // CNPJ (14 digits, with or without punctuation)
        $out = preg_replace('/\\b\\d{2}\\.?\\d{3}\\.?\\d{3}\\/?\\d{4}-?\\d{2}\\b/', '[cnpj]', $out) ?? $out;

        // Brazilian phone numbers (very loose)
        $out = preg_replace('/\\b\\(?\\d{2}\\)?\\s?9?\\d{4}-?\\d{4}\\b/', '[fone]', $out) ?? $out;

        return $out;
    }

    /**
     * @param  array<int, mixed>  $suggestions
     * @return array<int, int> learned entry ids
     */
    private function autoLearn(AiConversation $conversation, string $userText, string $assistantText, array $suggestions, string $provider): array
    {
        $max = (int) config('ai.auto_learn.max_suggestions', 3);
        $defaultActive = (bool) config('ai.auto_learn.default_active', false);

        $items = [];

        foreach (array_slice($suggestions, 0, $max) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = $this->stringifyAiTitle(Arr::get($item, 'title', ''));
            $content = $this->stringifyAiContent(Arr::get($item, 'content', ''));
            $tags = $this->stringifyAiTags(Arr::get($item, 'tags', ''));

            if ($title === '' || $content === '') {
                continue;
            }

            $items[] = compact('title', 'content', 'tags');
        }

        // Fallback heurística: apenas quando o provedor for gerativo (não local).
        if ($items === [] && in_array($provider, ['openai', 'ollama'], true) && $this->looksLikeProcedure($assistantText)) {
            $items[] = [
                'title' => Str::limit($userText !== '' ? $userText : 'Procedimento', 80, ''),
                'content' => $assistantText,
                'tags' => 'assistente, rascunho',
            ];
        }

        $ids = [];
        foreach (array_slice($items, 0, $max) as $item) {
            $fingerprint = sha1(Str::lower(trim($item['title'])."\n".trim($item['content'])));

            $existing = AiKnowledgeEntry::query()
                ->where('fingerprint', $fingerprint)
                ->first();

            if ($existing) {
                $ids[] = (int) $existing->getKey();
                continue;
            }

            $entry = AiKnowledgeEntry::create([
                'title' => Str::limit($item['title'], 255, ''),
                'content' => $item['content'],
                'tags' => Str::limit($item['tags'] ?? null, 255, ''),
                'is_active' => $defaultActive,
                'fingerprint' => $fingerprint,
                'source_type' => 'conversation',
                'source_ref' => (string) $conversation->getKey(),
            ]);

            $ids[] = (int) $entry->getKey();
        }

        return $ids;
    }

    /**
     * Normalize AI/provider fields that may come as arrays (e.g. tags as ["a","b"]).
     * This avoids "Array to string conversion" notices while keeping readable output.
     */
    private function stringifyAiParts(mixed $value, string $glue): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
            return trim((string) $value);
        }

        if ($value instanceof \Stringable) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            $parts = [];
            foreach ($value as $v) {
                if ($v === null) {
                    continue;
                }
                if (is_string($v) || is_int($v) || is_float($v) || is_bool($v) || $v instanceof \Stringable) {
                    $s = trim((string) $v);
                    if ($s !== '') {
                        $parts[] = $s;
                    }
                }
            }

            if ($parts !== []) {
                return implode($glue, $parts);
            }

            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return is_string($json) ? $json : '';
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '';
    }

    private function stringifyAiTitle(mixed $value): string
    {
        return $this->stringifyAiParts($value, ' ');
    }

    private function stringifyAiContent(mixed $value): string
    {
        return $this->stringifyAiParts($value, "\n");
    }

    private function stringifyAiTags(mixed $value): string
    {
        return $this->stringifyAiParts($value, ', ');
    }

    /**
     * @return array<int, string>
     */
    private function keywords(string $text): array
    {
        $text = Str::lower($text);
        $text = preg_replace('/[^\\p{L}\\p{N}\\s]/u', ' ', $text) ?? $text;
        $parts = preg_split('/\\s+/', trim($text)) ?: [];

        $stop = [
            'a', 'o', 'os', 'as', 'um', 'uma', 'uns', 'umas',
            'de', 'do', 'da', 'dos', 'das', 'em', 'no', 'na', 'nos', 'nas',
            'para', 'por', 'com', 'sem', 'e', 'ou',
            'como', 'que', 'qual', 'quais', 'quando', 'onde', 'porque', 'por que',
            'pra', 'pro',
        ];

        $words = [];
        foreach ($parts as $p) {
            if ($p === '' || in_array($p, $stop, true)) {
                continue;
            }
            if (strlen($p) < 4) {
                continue;
            }
            $words[] = $p;
        }

        $words = array_values(array_unique($words));
        usort($words, fn ($a, $b) => strlen($b) <=> strlen($a));

        return array_slice($words, 0, 4);
    }

    private function looksLikeProcedure(string $text): bool
    {
        $t = trim($text);
        if (strlen($t) < 250) {
            return false;
        }

        if (substr_count($t, "\n") < 3) {
            return false;
        }

        // Bullet/step patterns
        if (preg_match('/(^|\\n)\\s*[-*]\\s+/m', $t)) {
            return true;
        }
        if (preg_match('/(^|\\n)\\s*\\d+\\./m', $t)) {
            return true;
        }

        return false;
    }
}
