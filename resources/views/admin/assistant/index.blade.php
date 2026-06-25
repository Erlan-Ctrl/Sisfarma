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
                                                @php
                                                    $sourceUrl = $source['url'] ?? '';
                                                @endphp
                                                @if (!empty($sourceUrl) && filter_var($sourceUrl, FILTER_VALIDATE_URL))
                                                    @php $sourceScheme = strtolower(parse_url($sourceUrl, PHP_URL_SCHEME) ?? ''); @endphp
                                                    @if (in_array($sourceScheme, ['http','https']))
                                                        <a
                                                            class="rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-slate-700 hover:bg-slate-100"
                                                            href="{{ $sourceUrl }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                        >
                                                            {{ $source['label'] ?? ($source['type'] ?? 'fonte') }}
                                                        </a>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if (! $isUser && ! empty($learnedIds))
                                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                                            <span class="font-semibold text-slate-700">Aprendido (rascunho):</span>
                                            @foreach ($learnedIds as $id)
                                                @php
                                                    $knowledgeUrl = Route::has('admin.knowledge.show') ? route('admin.knowledge.show', $id) : url('/admin/conhecimentos/' . $id);
                                                @endphp
                                                <a class="rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold text-brand-800 hover:bg-slate-100" href="{{ $knowledgeUrl }}">
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
                                <textarea id="chat-input" class="min-h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus[...]
