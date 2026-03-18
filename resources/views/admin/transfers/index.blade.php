@extends('layouts.admin')

@section('title', 'Transferências | Admin')
@section('heading', 'Transferências')
@section('subtitle', 'Movimentação entre filiais')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-[18rem] flex-1">
                <form method="get" action="{{ route('admin.transfers.index') }}" class="grid gap-3 md:grid-cols-4">
                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Origem</span>
                        <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="from_store_id">
                            <option value="0" @selected($fromStoreId === 0)>Todas</option>
                            @foreach ($stores as $s)
                                <option value="{{ $s->getKey() }}" @selected((int) $fromStoreId === (int) $s->getKey())>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Destino</span>
                        <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="to_store_id">
                            <option value="0" @selected($toStoreId === 0)>Todas</option>
                            @foreach ($stores as $s)
                                <option value="{{ $s->getKey() }}" @selected((int) $toStoreId === (int) $s->getKey())>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2 md:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Busca</span>
                        <input class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" name="q" value="{{ $q }}" placeholder="Referência (opcional)">
                    </label>

                    <div class="flex flex-wrap items-center gap-2 md:col-span-4">
                        <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Filtrar
                        </button>
                        @if ($q !== '' || $fromStoreId > 0 || $toStoreId > 0)
                            <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-50 grid place-items-center" href="{{ route('admin.transfers.index') }}">
                                Limpar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.transfers.create') }}">
                    Nova transferência
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h2 class="text-base font-semibold tracking-tight">Histórico</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-white">
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <th class="px-5 py-3">ID</th>
                            <th class="px-5 py-3">Data</th>
                            <th class="px-5 py-3">Origem</th>
                            <th class="px-5 py-3">Destino</th>
                            <th class="px-5 py-3">Itens</th>
                            <th class="px-5 py-3">Responsável</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($transfers as $t)
                            <tr>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">#{{ $t->getKey() }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ optional($t->occurred_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $t->fromStore?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $t->toStore?->name ?? '-' }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ (int) $t->items_count }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $t->user?->name ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.transfers.show', $t) }}">
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="7">Nenhuma transferência registrada ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
@endsection

