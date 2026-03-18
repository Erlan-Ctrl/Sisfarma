@extends('layouts.admin')

@section('title', 'Scanner | Admin')
@section('heading', 'Scanner')
@section('subtitle', 'Leitura de código de barras (EAN/SKU)')

@section('content')
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight">Escanear</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Use o leitor de código de barras (ele digita e pressiona Enter) ou informe o EAN/SKU manualmente.
                </p>

                <form class="mt-5" method="get" action="{{ route('admin.scanner') }}">
                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Código</span>
                        <input
                            id="barcode-input"
                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-lg shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            type="text"
                            name="code"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="Escaneie aqui..."
                            value="{{ $code }}"
                            autofocus
                        >
                    </label>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button class="rounded-2xl bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Buscar
                        </button>
                        <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.scanner') }}">
                            Limpar
                        </a>
                        <a class="ml-auto text-sm font-semibold text-brand-800 hover:text-brand-900" href="{{ route('admin.products.index') }}">
                            Ir para produtos
                        </a>
                    </div>
                </form>
            </div>

            @if ($code !== '')
                <div class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold tracking-tight">Resultado</h2>

                    @if ($product)
                        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produto encontrado</p>
                                    <p class="mt-2 text-lg font-semibold tracking-tight">{{ $product->name }}</p>
                                    <p class="mt-2 text-sm text-slate-600">
                                        EAN: <span class="font-mono text-xs text-slate-800">{{ $product->ean ?: '-' }}</span>
                                        <span class="mx-2 text-slate-300">|</span>
                                        SKU: <span class="font-mono text-xs text-slate-800">{{ $product->sku ?: '-' }}</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if (Route::has('admin.inventory.movements.create'))
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.inventory.movements.create', ['product_id' => $product->getKey()]) }}">
                                            Movimentar estoque
                                        </a>
                                    @endif
                                    @if (Route::has('admin.purchases.create'))
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.purchases.create', ['product_id' => $product->getKey()]) }}">
                                            Compra
                                        </a>
                                    @endif
                                    @if (Route::has('admin.sales.create'))
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.sales.create', ['product_id' => $product->getKey()]) }}">
                                            Venda
                                        </a>
                                    @endif
                                    <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.products.show', $product) }}">
                                        Ver
                                    </a>
                                    <a class="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" href="{{ route('admin.products.edit', $product) }}">
                                        Editar
                                    </a>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                @if ($product->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-800">Ativo</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2 py-1 font-semibold text-slate-800">Inativo</span>
                                @endif
                                @if ($product->is_featured)
                                    <span class="rounded-full bg-indigo-100 px-2 py-1 font-semibold text-indigo-800">Destaque</span>
                                @endif
                                @if ($product->requires_prescription)
                                    <span class="rounded-full bg-amber-100 px-2 py-1 font-semibold text-amber-800">Requer receita</span>
                                @endif
                                @if ($product->categories->isNotEmpty())
                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-1 font-semibold text-slate-700">
                                        {{ $product->categories->pluck('name')->take(3)->join(', ') }}@if($product->categories->count() > 3)...@endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                            <p class="text-sm font-semibold text-amber-900">Nenhum produto encontrado para este código.</p>
                            <p class="mt-2 text-sm text-amber-800">
                                Código informado: <span class="font-mono text-xs">{{ $code }}</span>
                                @if ($digits !== '' && $digits !== $code)
                                    <span class="mx-2 text-amber-300">|</span>
                                    Dígitos: <span class="font-mono text-xs">{{ $digits }}</span>
                                @endif
                            </p>
                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <a
                                    class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                                    href="{{ route('admin.products.create', ['ean' => $digits !== '' ? $digits : $code]) }}"
                                >
                                    Cadastrar produto com este código
                                </a>
                                <a class="text-sm font-semibold text-amber-900 hover:text-amber-950" href="{{ route('admin.products.index', ['q' => $digits !== '' ? $digits : $code]) }}">
                                    Tentar buscar no catálogo
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <aside class="lg:col-span-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight">Boas práticas</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-700">
                    <li>Mantenha o cursor no campo de código para agilizar a leitura.</li>
                    <li>Se o produto não existir, use o botão de cadastro com EAN pré-preenchido.</li>
                    <li>Padronize o preenchimento de EAN/SKU para evitar duplicidade.</li>
                </ul>
            </div>
        </aside>
    </div>

    <script>
        (function () {
            const input = document.getElementById('barcode-input');
            if (!input) return;
            input.addEventListener('focus', () => input.select());
            window.addEventListener('pageshow', () => input.focus());
        })();
    </script>
@endsection
