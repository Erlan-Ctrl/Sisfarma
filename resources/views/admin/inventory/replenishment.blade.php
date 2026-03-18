@extends('layouts.admin')

@section('title', 'Reposição | Admin')
@section('heading', 'Reposição')
@section('subtitle', 'Análise por estoque mínimo')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="min-w-[16rem]">
                <form method="get" action="{{ route('admin.inventory.replenishment') }}" class="grid gap-2">
                    <label class="text-sm font-medium text-slate-700">Loja</label>
                    <div class="flex items-center gap-2">
                        <select class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id">
                            <option value="0" @selected(!$store)>Todas</option>
                            @foreach ($stores as $s)
                                <option value="{{ $s->getKey() }}" @selected($store && $store->getKey() === $s->getKey())>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="mode" value="{{ $mode }}">
                        <button class="h-12 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Ver
                        </button>
                    </div>
                </form>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.index', ['store_id' => optional($store)->getKey()]) }}">
                    Estoque
                </a>
                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => optional($store)->getKey()]) }}">
                    Movimentar
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-5 py-4">
                @php
                    $storeId = (int) request('store_id', 0);
                @endphp

                <a
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100 {{ $mode === 'below_min' ? 'border-brand-300 bg-white text-brand-900' : 'bg-white text-slate-700' }}"
                    href="{{ route('admin.inventory.replenishment', ['store_id' => $storeId, 'mode' => 'below_min']) }}"
                >
                    Abaixo do mínimo
                </a>
                <a
                    class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100 {{ $mode === 'excess' ? 'border-brand-300 bg-white text-brand-900' : 'bg-white text-slate-700' }}"
                    href="{{ route('admin.inventory.replenishment', ['store_id' => $storeId, 'mode' => 'excess']) }}"
                >
                    Excessos
                </a>

                <div class="ml-auto text-sm text-slate-600">
                    {{ $inventories->total() }} itens
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white">
                        <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <th class="px-5 py-3">Loja</th>
                            <th class="px-5 py-3">Produto</th>
                            <th class="px-5 py-3">EAN</th>
                            <th class="px-5 py-3">Qtd</th>
                            <th class="px-5 py-3">Mínimo</th>
                            <th class="px-5 py-3">{{ $mode === 'excess' ? 'Excesso' : 'Necessário' }}</th>
                            <th class="px-5 py-3">Último custo</th>
                            <th class="px-5 py-3">Compra</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($inventories as $inv)
                            @php
                                $p = $inv->product;
                                $min = $inv->min_quantity ?? 0;
                                $needed = max(0, $min - (int) $inv->quantity);
                                $excess = max(0, (int) $inv->quantity - ($min * 3));
                                $badge = $mode === 'excess'
                                    ? 'text-emerald-800 border-emerald-200 bg-emerald-50'
                                    : 'text-amber-800 border-amber-200 bg-amber-50';
                                $qtyBadge = $mode === 'excess'
                                    ? 'text-slate-800'
                                    : ((int) $inv->quantity <= 0 ? 'text-rose-800' : 'text-slate-800');
                            @endphp
                            <tr>
                                <td class="px-5 py-4 text-slate-700">
                                    <span class="font-semibold">{{ $inv->store?->name ?? '-' }}</span>
                                </td>
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
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold {{ $qtyBadge }}">
                                        {{ (int) $inv->quantity }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $inv->min_quantity !== null ? (int) $inv->min_quantity : '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badge }}">
                                        {{ $mode === 'excess' ? $excess : $needed }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $inv->last_unit_cost !== null ? number_format((float) $inv->last_unit_cost, 2, ',', '.') : '-' }}</td>
                                <td class="px-5 py-4 text-xs text-slate-500">{{ $inv->last_purchase_at ? $inv->last_purchase_at->format('d/m/Y') : '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.movements.index', ['store_id' => $inv->store?->getKey(), 'product_id' => $p?->getKey()]) }}">
                                            Histórico
                                        </a>
                                        <a class="rounded-xl bg-brand-700 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => $inv->store?->getKey(), 'product_id' => $p?->getKey()]) }}">
                                            Movimentar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="9">
                                    Nenhum item para exibir.
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
    </div>
@endsection

