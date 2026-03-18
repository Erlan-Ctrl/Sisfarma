@extends('layouts.admin')

@section('title', 'Produto | Admin')
@section('heading', 'Produto')
@section('subtitle', $product->name)

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Nome</dt>
                <dd class="mt-1 font-semibold">{{ $product->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Slug</dt>
                <dd class="mt-1 font-mono text-xs">{{ $product->slug }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Preço</dt>
                <dd class="mt-1">{{ $product->price !== null ? 'R$ '.number_format((float) $product->price, 2, ',', '.') : '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Status</dt>
                <dd class="mt-1">
                    {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                    @if ($product->is_featured)
                        , destaque
                    @endif
                    @if ($product->requires_prescription)
                        , requer receita
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">SKU</dt>
                <dd class="mt-1 font-mono text-xs">{{ $product->sku ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">EAN</dt>
                <dd class="mt-1 font-mono text-xs">{{ $product->ean ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Fornecedor</dt>
                <dd class="mt-1">{{ $product->supplier?->name ?: '-' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Categorias</dt>
                <dd class="mt-2 flex flex-wrap gap-2">
                    @forelse ($product->categories as $category)
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $category->name }}</span>
                    @empty
                        <span class="text-slate-600">-</span>
                    @endforelse
                </dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Descrição curta</dt>
                <dd class="mt-1 text-slate-700">{{ $product->short_description ?: '-' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Descrição</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $product->description ?: '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" href="{{ route('admin.products.edit', $product) }}">
                Editar
            </a>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.products.index') }}">
                Voltar
            </a>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
            <h2 class="text-base font-semibold tracking-tight">Estoque por loja</h2>
            <p class="mt-1 text-sm text-slate-600">Saldo, mínimo e último custo registrado em cada filial.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-white">
                    <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <th class="px-6 py-3">Loja</th>
                        <th class="px-6 py-3">Quantidade</th>
                        <th class="px-6 py-3">Mínimo</th>
                        <th class="px-6 py-3">Último custo</th>
                        <th class="px-6 py-3">Última compra</th>
                        <th class="px-6 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @php
                        $stores = $stores ?? collect();
                        $invByStoreId = $product->inventories->keyBy('store_id');
                    @endphp

                    @forelse ($stores as $store)
                        @php
                            $inv = $invByStoreId->get($store->getKey());
                            $qty = (int) ($inv?->quantity ?? 0);
                            $min = $inv?->min_quantity !== null ? (int) $inv->min_quantity : null;

                            $isBelow = $min !== null && $qty <= $min;
                            $isZero = $qty <= 0;
                            $badgeClass = $isZero
                                ? 'text-rose-800 border-rose-200 bg-rose-50'
                                : ($isBelow ? 'text-amber-800 border-amber-200 bg-amber-50' : 'text-slate-800 border-slate-200 bg-white');
                        @endphp
                        <tr class="{{ $isZero ? 'bg-rose-50/40' : ($isBelow ? 'bg-amber-50/40' : '') }}">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $store->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                                    {{ $qty }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700">{{ $min !== null ? $min : '-' }}</td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $inv?->last_unit_cost !== null ? 'R$ '.number_format((float) $inv->last_unit_cost, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">{{ optional($inv?->last_purchase_at)->format('d/m/Y H:i') ?: '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.movements.index', ['store_id' => $store->getKey(), 'product_id' => $product->getKey()]) }}">
                                    Histórico
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-6 text-slate-600" colspan="6">Nenhuma loja ativa cadastrada ainda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
