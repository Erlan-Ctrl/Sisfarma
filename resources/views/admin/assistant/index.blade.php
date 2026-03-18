@extends('layouts.admin')

@section('title', 'Assistente IA | Admin')
@section('heading', 'Assistente IA')
@section('subtitle', 'Ajuda contextual para a equipe')

@section('content')
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <div>
                        <p class="text-sm font-semibold">Conversa</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Baseado em cadastros, conhecimento e histórico. Provedor:
                            <span class="font-semibold">{{ $aiProvider ?? 'local' }}</span>.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form action="{{ route('admin.assistant.reset') }}" method="post" onsubmit="return confirm('Reiniciar conversa?')">
                            @csrf
                            <button class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100" type="submit">
                                Reiniciar
                            </button>
                        </form>
                        <a class="rounded-xl bg-brand-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.knowledge.index') }}">
                            Conhecimento
                        </a>
                    </div>
                </div>

                <div id="chat-scroll" class="max-h-[60vh] overflow-y-auto px-5 py-5">
                    <div id="chat-messages" class="grid gap-3">
                        @forelse ($messages as $msg)
                            @php
                                $isUser = $msg->role === 'user';
                                $meta = is_array($msg->meta) ? $msg->meta : [];
                                $sources = ! $isUser ? (array) ($meta['sources'] ?? []) : [];
                                $learnedIds = ! $isUser ? (array) ($meta['learned_entry_ids'] ?? []) : [];
                                $providerError = ! $isUser ? (string) ($meta['provider_error'] ?? '') : '';
                            @endphp
                            <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[48rem] rounded-2xl px-4 py-3 text-sm shadow-sm {{ $isUser ? 'bg-brand-700 text-white' : 'bg-slate-50 text-slate-900' }}">
                                    <div class="whitespace-pre-line leading-relaxed">{!! nl2br(e($msg->content)) !!}</div>
                                    <div class="mt-2 text-[11px] opacity-70">
                                        {{ $isUser ? 'Você' : 'Assistente' }} - {{ optional($msg->created_at)->format('d/m/Y H:i') }}
                                    </div>

                                    @if (! $isUser && $providerError !== '')
                                        @php
                                            $errLower = strtolower($providerError);
                                            $isSsl = str_contains($errLower, 'curl error 60') || str_contains($errLower, 'ssl certificate');
                                            $isQuota = str_contains($errLower, 'insufficient_quota') || str_contains($errLower, 'exceeded your current quota') || str_contains($errLower, 'quota');
                                        @endphp
                                        <div class="mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800">
                                            <div class="font-semibold">Falha ao chamar o provedor de IA; usando resposta local.</div>
                                            <div class="mt-1 break-words">{{ $providerError }}</div>
                                            @if ($isSsl)
                                                <div class="mt-2 text-rose-700">
                                                    Dica: no Windows, configure CA no PHP (curl.cainfo/openssl.cafile) ou defina <code class="font-mono">AI_OPENAI_VERIFY_SSL=false</code> no <code class="font-mono">.env</code> (apenas desenvolvimento).
                                                </div>
                                            @elseif ($isQuota)
                                                <div class="mt-2 text-rose-700">
                                                    Dica: parece falta de crédito/quota na OpenAI. Verifique billing/limites do projeto e use uma chave com acesso à API.
                                                </div>
                                            @else
                                                <div class="mt-2 text-rose-700">
                                                    @if (($aiProvider ?? 'local') === 'ollama')
                                                        Dica: inicie o Ollama (app ou <code class="font-mono">ollama serve</code>) e verifique <code class="font-mono">AI_OLLAMA_URL</code>/<code class="font-mono">AI_OLLAMA_MODEL</code>.
                                                    @else
                                                        Dica: verifique <code class="font-mono">OPENAI_API_KEY</code>, modelo e conectividade.
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if (! $isUser && ! empty($sources))
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                            @foreach ($sources as $source)
                                                @if (!empty($source['url']))
                                                    <a class="rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-700 hover:bg-slate-100" href="{{ $source['url'] }}">
                                                        {{ $source['label'] ?? ($source['type'] ?? 'fonte') }}
                                                    </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (! $isUser && ! empty($learnedIds))
                                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                            <span class="font-semibold text-slate-700">Aprendido (rascunho):</span>
                                            @foreach ($learnedIds as $id)
                                                <a class="rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-brand-800 hover:bg-slate-100" href="{{ url('/admin/conhecimentos/'.$id) }}">
                                                    #{{ $id }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-600">
                                Comece digitando uma pergunta, um procedimento ou cole um EAN (apenas números) para localizar um produto.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-white px-5 py-4">
                    <form id="chat-form" class="flex flex-col gap-3 md:flex-row md:items-end" action="{{ route('admin.assistant.send') }}" method="post">
                        @csrf
                        <div class="flex-1">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Mensagem</span>
                                <textarea id="chat-input" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="message" placeholder="Ex.: Como cadastrar um produto? / 7891234567890"></textarea>
                            </label>
                            <p class="mt-2 text-xs text-slate-500">
                                Dica: o assistente pode salvar rascunhos no <span class="font-semibold">Conhecimento</span> automaticamente.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="chat-send" class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                                Enviar
                            </button>
                            <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.scanner') }}">
                                Scanner
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <aside class="lg:col-span-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight">Sugestões</h2>
                <div class="mt-4 grid gap-2 text-sm">
                    <button class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left font-semibold hover:bg-slate-100" type="button" data-suggestion="Como cadastrar um produto novo?">
                        Como cadastrar um produto novo?
                    </button>
                    <button class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left font-semibold hover:bg-slate-100" type="button" data-suggestion="Como ativar/desativar um produto?">
                        Como ativar/desativar um produto?
                    </button>
                    <button class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-left font-semibold hover:bg-slate-100" type="button" data-suggestion="Quais ofertas estão ativas hoje?">
                        Quais ofertas estão ativas hoje?
                    </button>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                    Se aparecer um rascunho bom em "Aprendido", revise e marque como ativo em <span class="font-semibold">Conhecimento</span>.
                </p>
            </div>
        </aside>
    </div>

    <script>
        (function () {
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-input');
            const sendBtn = document.getElementById('chat-send');
            const messages = document.getElementById('chat-messages');
            const scroll = document.getElementById('chat-scroll');
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const requestedProvider = @json($aiProvider ?? 'local');

            function scrollToBottom() {
                if (!scroll) return;
                scroll.scrollTop = scroll.scrollHeight;
            }

            function addBubble(role, content, when, metaData) {
                const wrapper = document.createElement('div');
                wrapper.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');

                const bubble = document.createElement('div');
                bubble.className =
                    'max-w-[48rem] rounded-2xl px-4 py-3 text-sm shadow-sm ' +
                    (role === 'user' ? 'bg-brand-700 text-white' : 'bg-slate-50 text-slate-900');

                const body = document.createElement('div');
                body.className = 'whitespace-pre-line leading-relaxed';
                body.textContent = content;

                const meta = document.createElement('div');
                meta.className = 'mt-2 text-[11px] opacity-70';
                meta.textContent = (role === 'user' ? 'Você' : 'Assistente') + ' - ' + when;

                bubble.appendChild(body);
                bubble.appendChild(meta);

                if (role === 'assistant' && metaData && metaData.provider_error) {
                    const err = document.createElement('div');
                    err.className = 'mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-800';

                    const title = document.createElement('div');
                    title.className = 'font-semibold';
                    title.textContent = 'Falha ao chamar o provedor de IA; usando resposta local.';

                    const detail = document.createElement('div');
                    detail.className = 'mt-1 break-words';
                    detail.textContent = String(metaData.provider_error);

                    const hint = document.createElement('div');
                    hint.className = 'mt-2 text-rose-700';

                    const lower = String(metaData.provider_error).toLowerCase();
                    if (lower.includes('curl error 60') || lower.includes('ssl certificate')) {
                        hint.textContent = 'Dica: no Windows, configure CA no PHP (curl.cainfo/openssl.cafile) ou defina AI_OPENAI_VERIFY_SSL=false no .env (apenas desenvolvimento).';
                    } else if (lower.includes('insufficient_quota') || lower.includes('exceeded your current quota') || lower.includes('quota')) {
                        hint.textContent = 'Dica: parece falta de crédito/quota na OpenAI. Verifique billing/limites do projeto e use uma chave com acesso à API.';
                    } else {
                        if (requestedProvider === 'ollama') {
                            hint.textContent = 'Dica: inicie o Ollama (app ou ollama serve) e verifique AI_OLLAMA_URL/AI_OLLAMA_MODEL.';
                        } else {
                            hint.textContent = 'Dica: verifique OPENAI_API_KEY, modelo e conectividade.';
                        }
                    }

                    err.appendChild(title);
                    err.appendChild(detail);
                    err.appendChild(hint);

                    bubble.appendChild(err);
                }

                if (role === 'assistant' && metaData && Array.isArray(metaData.sources) && metaData.sources.length) {
                    const sources = document.createElement('div');
                    sources.className = 'mt-3 flex flex-wrap gap-2 text-xs';
                    metaData.sources.forEach((s) => {
                        if (!s || !s.url) return;
                        const a = document.createElement('a');
                        a.href = s.url;
                        a.className = 'rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-700 hover:bg-slate-100';
                        a.textContent = s.label || s.type || 'fonte';
                        sources.appendChild(a);
                    });
                    if (sources.childNodes.length) bubble.appendChild(sources);
                }

                if (role === 'assistant' && metaData && Array.isArray(metaData.learned_entry_ids) && metaData.learned_entry_ids.length) {
                    const learned = document.createElement('div');
                    learned.className = 'mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-600';
                    const label = document.createElement('span');
                    label.className = 'font-semibold text-slate-700';
                    label.textContent = 'Aprendido (rascunho):';
                    learned.appendChild(label);
                    metaData.learned_entry_ids.forEach((id) => {
                        const a = document.createElement('a');
                        a.href = '/admin/conhecimentos/' + id;
                        a.className = 'rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-brand-800 hover:bg-slate-100';
                        a.textContent = '#' + id;
                        learned.appendChild(a);
                    });
                    bubble.appendChild(learned);
                }

                wrapper.appendChild(bubble);
                messages.appendChild(wrapper);
                scrollToBottom();
            }

            document.querySelectorAll('[data-suggestion]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    input.value = btn.getAttribute('data-suggestion') || '';
                    input.focus();
                });
            });

            scrollToBottom();
            input.focus();

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const text = (input.value || '').trim();
                if (!text) return;

                input.value = '';
                addBubble('user', text, 'agora', null);

                sendBtn.disabled = true;
                sendBtn.textContent = 'Enviando...';

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({ message: text }),
                    });

                    const data = await res.json();
                    if (!res.ok || !data.ok) {
                        throw new Error('Falha ao enviar.');
                    }

                    addBubble('assistant', data.assistant_message.content, 'agora', data.assistant_message.meta || null);
                } catch (err) {
                    addBubble('assistant', 'Erro ao responder. Tente novamente.', 'agora', null);
                } finally {
                    sendBtn.disabled = false;
                    sendBtn.textContent = 'Enviar';
                    input.focus();
                }
            });
        })();
    </script>
@endsection
