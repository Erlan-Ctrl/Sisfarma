@extends('layouts.admin')

@section('title', 'Estoque | Admin')
@section('heading', 'Estoque')
@section('subtitle', 'Controle por loja')

@section('content')
    <div class="grid gap-6">
        @if ($stores->isNotEmpty())
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-[16rem]">
                <form method="get" action="{{ route('admin.inventory.index') }}" class="grid gap-2">
                    <label class="text-sm font-medium text-slate-700">Loja</label>
                    <div class="flex items-center gap-2">
                        <select class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id">
                            @foreach ($stores as $s)
                                <option value="{{ $s->getKey() }}" @selected($store && $store->getKey() === $s->getKey())>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <button class="h-12 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Ver
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.replenishment', ['store_id' => optional($store)->getKey(), 'mode' => 'below_min']) }}">
                    Reposição
                </a>
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.movements.index', ['store_id' => optional($store)->getKey()]) }}">
                    Movimentações
                </a>
                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => optional($store)->getKey()]) }}">
                    Movimentar
                </a>
            </div>
        </div>

        @endif

        @if ($stores->isEmpty())
            <div class="rounded-3xl border border-slate-200 bg-white p-6 text-sm text-slate-700 shadow-sm">
                Nenhuma loja cadastrada. Cadastre uma loja para controlar estoque.
                <div class="mt-4">
                    <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.stores.create') }}">
                        Cadastrar loja
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <form class="flex flex-1 flex-wrap items-center gap-2" method="get" action="{{ route('admin.inventory.index') }}">
                        <input type="hidden" name="store_id" value="{{ optional($store)->getKey() }}">
                        <input
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100 md:w-80"
                            type="search"
                            name="q"
                            placeholder="Buscar por produto, EAN ou SKU"
                            value="{{ $q }}"
                        >
                        <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="filter">
                            <option value="all" @selected($filter === 'all')>Todos</option>
                            <option value="below_min" @selected($filter === 'below_min')>Abaixo do mínimo</option>
                            <option value="zero" @selected($filter === 'zero')>Zerado</option>
                        </select>
                        <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Filtrar
                        </button>
                        @if ($q !== '' || $filter !== 'all')
                            <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.inventory.index', ['store_id' => optional($store)->getKey()]) }}">
                                Limpar
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white">
                            <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                                <th class="px-5 py-3">Produto</th>
                                <th class="px-5 py-3">EAN</th>
                                <th class="px-5 py-3">Quantidade</th>
                                <th class="px-5 py-3">Mínimo</th>
                                <th class="px-5 py-3">Atualizado</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($inventories as $inv)
                                @php
                                    $p = $inv->product;
                                    $isBelow = $inv->min_quantity !== null && $inv->quantity <= $inv->min_quantity;
                                    $isZero = $inv->quantity <= 0;
                                @endphp
                                <tr class="{{ $isZero ? 'bg-rose-50/60' : ($isBelow ? 'bg-amber-50/60' : '') }}">
                                    <td class="px-5 py-4">
                                        <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ $p ? route('admin.products.show', $p) : '#' }}">
                                            {{ $p?->name ?? 'Produto removido' }}
                                        </a>
                                        @if ($p?->sku)
                                            <div class="mt-1 text-xs text-slate-500">SKU: <span class="font-mono">{{ $p->sku }}</span></div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $p?->ean ?: '-' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold {{ $isZero ? 'text-rose-800' : ($isBelow ? 'text-amber-800' : 'text-slate-800') }}">
                                            {{ $inv->quantity }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $inv->min_quantity !== null ? $inv->min_quantity : '-' }}</td>
                                    <td class="px-5 py-4 text-xs text-slate-500">{{ optional($inv->updated_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.movements.index', ['store_id' => optional($store)->getKey(), 'product_id' => $p?->getKey()]) }}">
                                                Histórico
                                            </a>
                                            <a class="rounded-xl bg-brand-700 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => optional($store)->getKey(), 'product_id' => $p?->getKey()]) }}">
                                                Movimentar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-5 py-6 text-slate-600" colspan="6">
                                        Nenhum registro de estoque ainda. Use <span class="font-semibold">Movimentar</span> para criar o primeiro.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 bg-white px-5 py-4">
                    {{ $inventories->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
