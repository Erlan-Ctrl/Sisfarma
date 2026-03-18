@extends('layouts.admin')

@section('title', 'Relatórios | Admin')
@section('heading', 'Relatórios')
@section('subtitle', 'Visão rápida e pendências')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <form class="flex flex-wrap items-end gap-2" method="get" action="{{ route('admin.reports.index') }}">
                <label class="grid gap-2">
                    <span class="text-sm font-medium text-slate-700">Loja</span>
                    <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id">
                        <option value="">Todas</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->getKey() }}" @selected($store && $store->getKey() === $s->getKey())>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Filtrar
                </button>
                @if ($storeId > 0)
                    <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.reports.index') }}">
                        Limpar
                    </a>
                @endif
            </form>

            <div class="text-sm text-slate-600">
                Última atualização: <span class="font-semibold text-slate-900">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-800/80">Estoque baixo</p>
                <p class="mt-2 text-2xl font-semibold text-amber-900">{{ $lowStock->count() }}</p>
                <p class="mt-1 text-xs text-amber-800/80">Abaixo do mínimo</p>
            </div>
            <div class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-rose-800/80">Zerado</p>
                <p class="mt-2 text-2xl font-semibold text-rose-900">{{ $zeroStock->count() }}</p>
                <p class="mt-1 text-xs text-rose-800/80">Sem estoque</p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produtos sem EAN</p>
                <p class="mt-2 text-2xl font-semibold">{{ $missingEan->count() }}</p>
                <p class="mt-1 text-xs text-slate-500">Cadastro incompleto</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <section class="lg:col-span-6">
                <div class="h-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Estoque baixo</h2>
                        <p class="mt-1 text-sm text-slate-600">Top 30 itens abaixo do mínimo.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-white">
                                <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                    <th class="px-5 py-3">Produto</th>
                                    <th class="px-5 py-3">Loja</th>
                                    <th class="px-5 py-3">Qtd.</th>
                                    <th class="px-5 py-3">Mín.</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($lowStock as $inv)
                                    <tr>
                                        <td class="px-5 py-4 align-top">
                                            @if ($inv->product)
                                                <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $inv->product) }}">{{ $inv->product->name }}</a>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    @if ($inv->product->ean)
                                                        EAN: <span class="font-mono">{{ $inv->product->ean }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-600">Produto removido</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 align-top text-slate-700">{{ $inv->store?->name ?? '-' }}</td>
                                        <td class="px-5 py-4 align-top font-mono text-xs text-amber-800">{{ (int) $inv->quantity }}</td>
                                        <td class="px-5 py-4 align-top font-mono text-xs text-slate-700">{{ $inv->min_quantity !== null ? (int) $inv->min_quantity : '-' }}</td>
                                        <td class="px-5 py-4 align-top">
                                            <div class="flex justify-end">
                                                <a class="rounded-xl bg-brand-700 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => $inv->store?->getKey(), 'product_id' => $inv->product?->getKey()]) }}">
                                                    Movimentar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-5 py-6 text-slate-600" colspan="5">Nenhum item abaixo do mínimo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="lg:col-span-6">
                <div class="h-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Zerados</h2>
                        <p class="mt-1 text-sm text-slate-600">Top 30 itens com quantidade 0.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-white">
                                <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                    <th class="px-5 py-3">Produto</th>
                                    <th class="px-5 py-3">Loja</th>
                                    <th class="px-5 py-3">Qtd.</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($zeroStock as $inv)
                                    <tr>
                                        <td class="px-5 py-4 align-top">
                                            @if ($inv->product)
                                                <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $inv->product) }}">{{ $inv->product->name }}</a>
                                                <div class="mt-1 text-xs text-slate-500">
                                                    @if ($inv->product->ean)
                                                        EAN: <span class="font-mono">{{ $inv->product->ean }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-600">Produto removido</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 align-top text-slate-700">{{ $inv->store?->name ?? '-' }}</td>
                                        <td class="px-5 py-4 align-top font-mono text-xs text-rose-800">{{ (int) $inv->quantity }}</td>
                                        <td class="px-5 py-4 align-top">
                                            <div class="flex justify-end">
                                                <a class="rounded-xl bg-brand-700 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => $inv->store?->getKey(), 'product_id' => $inv->product?->getKey()]) }}">
                                                    Movimentar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-5 py-6 text-slate-600" colspan="4">Nenhum item zerado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <section class="lg:col-span-5">
                <div class="h-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Produtos sem EAN</h2>
                        <p class="mt-1 text-sm text-slate-600">Top 30 cadastros que precisam de EAN.</p>
                    </div>
                    <div class="divide-y divide-slate-200">
                        @forelse ($missingEan as $p)
                            <a class="block px-6 py-4 hover:bg-slate-50" href="{{ route('admin.products.edit', $p) }}">
                                <div class="font-semibold text-slate-900">{{ $p->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">SKU: <span class="font-mono">{{ $p->sku ?: '-' }}</span></div>
                            </a>
                        @empty
                            <div class="px-6 py-6 text-sm text-slate-600">Nenhum produto pendente.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="lg:col-span-7">
                <div class="h-full overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Movimentações recentes</h2>
                        <p class="mt-1 text-sm text-slate-600">Últimas 20 movimentações de estoque.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-white">
                                <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                    <th class="px-5 py-3">Data</th>
                                    <th class="px-5 py-3">Loja</th>
                                    <th class="px-5 py-3">Produto</th>
                                    <th class="px-5 py-3">Tipo</th>
                                    <th class="px-5 py-3">Delta</th>
                                    <th class="px-5 py-3">Resp.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @forelse ($recentMovements as $m)
                                    @php
                                        $typeLabel = match($m->type) {
                                            'in' => 'Entrada',
                                            'out' => 'Saída',
                                            'adjust' => 'Ajuste',
                                            default => $m->type,
                                        };
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-4 text-xs text-slate-600">{{ optional($m->occurred_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-5 py-4">{{ $m->store?->name ?? '-' }}</td>
                                        <td class="px-5 py-4">
                                            @if ($m->product)
                                                <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $m->product) }}">{{ $m->product->name }}</a>
                                            @else
                                                <span class="text-slate-600">Produto removido</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-slate-700">{{ $typeLabel }}</td>
                                        <td class="px-5 py-4 font-mono text-xs {{ $m->delta < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                            {{ $m->delta > 0 ? '+' : '' }}{{ $m->delta }}
                                        </td>
                                        <td class="px-5 py-4 text-slate-700">{{ $m->user?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="px-5 py-6 text-slate-600" colspan="6">Nenhuma movimentação recente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-slate-200 bg-white px-6 py-4">
                        <a class="text-sm font-semibold text-brand-800 hover:text-brand-900" href="{{ route('admin.inventory.movements.index', ['store_id' => optional($store)->getKey()]) }}">
                            Ver histórico completo
                        </a>
                    </div>
                </div>
            </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-base font-semibold tracking-tight">Compras recentes</h2>
                    <p class="mt-1 text-sm text-slate-600">Últimas 10 entradas.</p>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($recentPurchases as $p)
                        <a class="block px-6 py-4 hover:bg-slate-50" href="{{ route('admin.purchases.show', $p) }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-slate-900">{{ $p->supplier?->name ?? '-' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ optional($p->occurred_at)->format('d/m/Y H:i') }} • {{ $p->store?->name ?? '-' }} • Itens: {{ (int) $p->items_count }}
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-slate-700">
                                    @if ((float) $p->total_cost > 0)
                                        R$ {{ number_format((float) $p->total_cost, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-6 text-sm text-slate-600">Nenhuma compra recente.</div>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-base font-semibold tracking-tight">Vendas recentes</h2>
                    <p class="mt-1 text-sm text-slate-600">Últimas 10 saídas.</p>
                </div>
                <div class="divide-y divide-slate-200">
                    @forelse ($recentSales as $s)
                        <a class="block px-6 py-4 hover:bg-slate-50" href="{{ route('admin.sales.show', $s) }}">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-slate-900">Venda #{{ $s->getKey() }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ optional($s->occurred_at)->format('d/m/Y H:i') }} • {{ $s->store?->name ?? '-' }} • Itens: {{ (int) $s->items_count }}
                                    </div>
                                </div>
                                <div class="text-sm font-semibold text-slate-700">
                                    @if ((float) $s->total_amount > 0)
                                        R$ {{ number_format((float) $s->total_amount, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-6 text-sm text-slate-600">Nenhuma venda recente.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
