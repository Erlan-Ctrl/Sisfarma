@extends('layouts.admin')

@section('title', 'Auditoria | Admin')
@section('heading', 'Auditoria')
@section('subtitle', 'Detalhes do evento')

@section('content')
    <div class="grid gap-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">ID</dt>
                    <dd class="mt-1 font-mono text-xs">#{{ $log->getKey() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Data</dt>
                    <dd class="mt-1">{{ optional($log->occurred_at)->format('d/m/Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Ação</dt>
                    <dd class="mt-1 font-semibold">{{ $log->action }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Usuário</dt>
                    <dd class="mt-1">
                        {{ $log->user?->name ?? '-' }}
                        @if ($log->user_id)
                            <span class="ml-2 font-mono text-xs text-slate-500">#{{ (int) $log->user_id }}</span>
                        @endif
                    </dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Alvo</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        @if ($log->auditable_type && $log->auditable_id)
                            {{ $log->auditable_type }}#{{ (int) $log->auditable_id }}
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">IP / User-Agent</dt>
                    <dd class="mt-1 font-mono text-xs text-slate-700">
                        {{ $log->ip ?: '-' }}
                        <span class="mx-2 text-slate-300">|</span>
                        {{ $log->user_agent ?: '-' }}
                    </dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.audit.index') }}">
                    Voltar
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-base font-semibold tracking-tight">Antes</h2>
                </div>
                <pre class="max-h-[32rem] overflow-auto p-6 text-xs text-slate-800">{{ json_encode($log->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-base font-semibold tracking-tight">Depois</h2>
                </div>
                <pre class="max-h-[32rem] overflow-auto p-6 text-xs text-slate-800">{{ json_encode($log->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h2 class="text-base font-semibold tracking-tight">Meta</h2>
            </div>
            <pre class="max-h-[24rem] overflow-auto p-6 text-xs text-slate-800">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
@endsection

