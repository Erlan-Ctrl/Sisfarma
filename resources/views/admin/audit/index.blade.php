@extends('layouts.admin')

@section('title', 'Auditoria | Admin')
@section('heading', 'Auditoria')
@section('subtitle', 'Histórico de ações')

@section('content')
    <div class="grid gap-6">
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h2 class="text-base font-semibold tracking-tight">Filtros</h2>
                <p class="mt-1 text-sm text-slate-600">Use para encontrar alterações de preço, estoque, vendas, compras etc.</p>
            </div>

            <div class="px-6 py-6">
                <form class="grid gap-3 md:grid-cols-4" method="get" action="{{ route('admin.audit.index') }}">
                    <label class="grid gap-2 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Busca</span>
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" name="q" value="{{ $q }}" placeholder="Ex.: product.updated, sale.posted, 10">
                    </label>

                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Ação</span>
                        <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="action">
                            <option value="">Todas</option>
                            @foreach ($actions as $a)
                                <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Usuário (ID)</span>
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" min="0" name="user_id" value="{{ $userId ?: '' }}" placeholder="0">
                    </label>

                    <div class="flex flex-wrap items-center gap-2 md:col-span-4">
                        <button class="h-12 rounded-2xl bg-brand-700 px-5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Filtrar
                        </button>
                        @if ($q !== '' || $action !== '' || $userId > 0)
                            <a class="h-12 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-semibold shadow-sm hover:bg-slate-50 grid place-items-center" href="{{ route('admin.audit.index') }}">
                                Limpar
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h2 class="text-base font-semibold tracking-tight">Eventos</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-white">
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <th class="px-5 py-3">Data</th>
                            <th class="px-5 py-3">Ação</th>
                            <th class="px-5 py-3">Usuário</th>
                            <th class="px-5 py-3">Alvo</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="px-5 py-4 text-xs text-slate-600">
                                    {{ optional($log->occurred_at)->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-slate-900">{{ $log->action }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    {{ $log->user?->name ?? '-' }}
                                    @if ($log->user_id)
                                        <span class="ml-2 font-mono text-xs text-slate-500">#{{ (int) $log->user_id }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    @if ($log->auditable_type && $log->auditable_id)
                                        <span class="font-mono text-xs">{{ class_basename($log->auditable_type) }}#{{ (int) $log->auditable_id }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.audit.show', $log) }}">
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="5">Nenhum evento encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection

