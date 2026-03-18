@extends('layouts.admin')

@section('title', 'Compras | Admin')
@section('heading', 'Compras')
@section('subtitle', 'Entrada de mercadorias')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <form class="flex flex-wrap items-end gap-2" method="get" action="{{ route('admin.purchases.index') }}">
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
                    <span class="text-sm font-medium text-slate-700">Fornecedor</span>
                    <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="supplier_id">
                        <option value="">Todos</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->getKey() }}" @selected((int) $supplierId === (int) $supplier->getKey())>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="grid gap-2">
                    <span class="text-sm font-medium text-slate-700">Referência</span>
                    <input class="h-11 w-56 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" name="q" value="{{ $q }}" placeholder="NF, pedido...">
                </label>

                <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Filtrar
                </button>
                @if ($q !== '' || $storeId > 0 || $supplierId > 0)
                    <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.purchases.index') }}">
                        Limpar
                    </a>
                @endif
            </form>

            <div class="flex flex-wrap items-center gap-2">
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.purchases.import_xml') }}">
                    Importar XML
                </a>
                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.purchases.create') }}">
                    Nova compra
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Data</th>
                            <th class="px-5 py-3">Loja</th>
                            <th class="px-5 py-3">Fornecedor</th>
                            <th class="px-5 py-3">Referência</th>
                            <th class="px-5 py-3">Itens</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Responsável</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($purchases as $purchase)
                            <tr>
                                <td class="px-5 py-4 text-xs text-slate-600">{{ optional($purchase->occurred_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4">{{ $purchase->store?->name ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.purchases.show', $purchase) }}">
                                        {{ $purchase->supplier?->name ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $purchase->reference ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ (int) $purchase->items_count }}</span>
                                </td>
                                <td class="px-5 py-4 text-slate-700">
                                    @if ((float) $purchase->total_cost > 0)
                                        R$ {{ number_format((float) $purchase->total_cost, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $purchase->user?->name ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.purchases.show', $purchase) }}">
                                            Ver
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="8">Nenhuma compra encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
@endsection
