@extends('layouts.admin')

@section('title', 'Produtos | Admin')
@section('heading', 'Produtos')
@section('subtitle', 'Gerencie o catálogo')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="text-sm text-slate-600">
                Total: <span class="font-semibold text-slate-900">{{ $products->total() }}</span>
                @if (!empty($q) || !empty($supplierId))
                    <span class="mx-2 text-slate-300">|</span>
                    Filtrado
                    @if (!empty($q))
                        por: <span class="font-semibold text-slate-900">"{{ $q }}"</span>
                    @endif
                    @if (!empty($supplierId))
                        @php
                            $selectedSupplier = $suppliers->firstWhere('id', (int) $supplierId);
                        @endphp
                        <span class="mx-2 text-slate-300">|</span>
                        Fornecedor: <span class="font-semibold text-slate-900">{{ $selectedSupplier?->name ?? '#' . $supplierId }}</span>
                    @endif
                    <a class="ml-2 font-semibold text-brand-800 hover:text-brand-900" href="{{ route('admin.products.index') }}">Limpar</a>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <form class="flex flex-wrap items-center gap-2" method="get" action="{{ route('admin.products.index') }}">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <select class="h-11 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="supplier_id">
                        <option value="">Todos os fornecedores</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->getKey() }}" @selected((int) $supplierId === (int) $supplier->getKey())>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    <button class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-50" type="submit">
                        Aplicar
                    </button>
                </form>

                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.products.create') }}">
                    Novo produto
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Produto</th>
                            <th class="px-5 py-3">Fornecedor</th>
                            <th class="px-5 py-3">Preço</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $product->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        @if ($product->ean)
                                            EAN: <span class="font-mono">{{ $product->ean }}</span>
                                        @else
                                            EAN: -
                                        @endif
                                        <span class="mx-2 text-slate-300">|</span>
                                        @if ($product->categories->isNotEmpty())
                                            {{ $product->categories->pluck('name')->take(3)->join(', ') }}@if($product->categories->count() > 3)...@endif
                                        @else
                                            Sem categoria
                                        @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $product->supplier?->name ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">
                                    @if ($product->price !== null)
                                        R$ {{ number_format((float) $product->price, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if ($product->is_active)
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Ativo</span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Inativo</span>
                                        @endif
                                        @if ($product->is_featured)
                                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-800">Destaque</span>
                                        @endif
                                        @if ($product->requires_prescription)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">Receita</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.products.show', $product) }}">Ver</a>
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.products.edit', $product) }}">Editar</a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="post" onsubmit="return confirm('Remover produto?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="submit">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="5">Nenhum produto cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
