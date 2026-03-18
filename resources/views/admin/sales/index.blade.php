@extends('layouts.admin')

@section('title', 'Vendas | Admin')
@section('heading', 'Vendas')
@section('subtitle', 'Saídas e atendimento')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <form class="flex flex-wrap items-end gap-2" method="get" action="{{ route('admin.sales.index') }}">
                <label class="grid gap-2">
                    <span class="text-sm font-medium text-slate-700">Loja</span>
                    <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id">
                        <option value="">Todas</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->getKey() }}" @selected((int) $storeId === (int) $s->getKey())>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-slate-700">Referência</span>
                    <input class="h-11 w-56 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" name="q" value="{{ $q }}" placeholder="Cupom, pedido...">
                </label>

                <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Filtrar
                </button>
                @if ($q !== '' || $storeId > 0)
                    <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.sales.index') }}">
                        Limpar
                    </a>
                @endif
            </form>

            <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.sales.create') }}">
                Nova venda
            </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Data</th>
                            <th class="px-5 py-3">Loja</th>
                            <th class="px-5 py-3">Pagamento</th>
                            <th class="px-5 py-3">Referência</th>
                            <th class="px-5 py-3">Itens</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Responsável</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="px-5 py-4 text-xs text-slate-600">{{ optional($sale->occurred_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4">{{ $sale->store?->name ?? '-' }}</td>
                                @php
                                    $pm = $sale->payment_method ? ($paymentMethods[$sale->payment_method] ?? $sale->payment_method) : null;
                                @endphp
                                <td class="px-5 py-4 text-slate-700">{{ $pm ?: '-' }}</td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $sale->reference ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ (int) $sale->items_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    @if ((float) $sale->total_amount > 0)
                                        R$ {{ number_format((float) $sale->total_amount, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $sale->user?->name ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.sales.show', $sale) }}">
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="8">Nenhuma venda encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
@endsection
