@extends('layouts.admin')

@section('title', 'Movimentações | Estoque')
@section('heading', 'Movimentações')
@section('subtitle', 'Histórico do estoque')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <form class="flex flex-wrap items-end gap-2" method="get" action="{{ route('admin.inventory.movements.index') }}">
                <label class="grid gap-2">
                    <span class="text-sm font-medium text-slate-700">Loja</span>
                    <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id">
                        <option value="">Todas</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->getKey() }}" @selected($store && $store->getKey() === $s->getKey())>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </label>

                @if ($product)
                    <input type="hidden" name="product_id" value="{{ $product->getKey() }}">
                @endif

                <button class="h-12 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Filtrar
                </button>
                <a class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.inventory.movements.index') }}">
                    Limpar
                </a>
            </form>

            <div class="flex items-center gap-2">
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.index', ['store_id' => optional($store)->getKey()]) }}">
                    Voltar ao estoque
                </a>
                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.inventory.movements.create', ['store_id' => optional($store)->getKey(), 'product_id' => optional($product)->getKey()]) }}">
                    Movimentar
                </a>
            </div>
        </div>

        @if ($product)
            <div class="rounded-3xl border border-slate-200 bg-white p-5 text-sm shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produto</span>
                    <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                    @if ($product->ean)
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">EAN: <span class="font-mono">{{ $product->ean }}</span></span>
                    @endif
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-white">
                        <tr class="border-b border-slate-200 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <th class="px-5 py-3">Data</th>
                            <th class="px-5 py-3">Loja</th>
                            <th class="px-5 py-3">Produto</th>
                            <th class="px-5 py-3">Tipo</th>
                            <th class="px-5 py-3">Delta</th>
                            <th class="px-5 py-3">Antes</th>
                            <th class="px-5 py-3">Depois</th>
                            <th class="px-5 py-3">Responsável</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($movements as $m)
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
                                        <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $m->product) }}">
                                            {{ $m->product->name }}
                                        </a>
                                    @else
                                        <span class="text-slate-600">Produto removido</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">{{ $typeLabel }}</td>
                                <td class="px-5 py-4 font-mono text-xs {{ $m->delta < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                    {{ $m->delta > 0 ? '+' : '' }}{{ $m->delta }}
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $m->quantity_before }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $m->quantity_after }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $m->user?->name ?? '-' }}</td>
                            </tr>
                            @if ($m->reason || $m->note)
                                <tr class="bg-slate-50/60">
                                    <td class="px-5 pb-4 text-xs text-slate-500" colspan="8">
                                        @if ($m->reason)
                                            <span class="font-semibold">Motivo:</span> {{ $m->reason }}
                                        @endif
                                        @if ($m->reason && $m->note)
                                            <span class="mx-2 text-slate-300">|</span>
                                        @endif
                                        @if ($m->note)
                                            <span class="font-semibold">Obs.:</span> {{ $m->note }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="8">Nenhuma movimentação encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
@endsection
