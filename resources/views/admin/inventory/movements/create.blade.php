@extends('layouts.admin')

@section('title', 'Movimentar Estoque | Admin')
@section('heading', 'Movimentar Estoque')
@section('subtitle', 'Entrada, saída e ajuste')

@section('content')
    <div class="grid gap-6 lg:grid-cols-12">
        <section class="lg:col-span-7">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                    <h2 class="text-base font-semibold tracking-tight">Nova movimentação</h2>
                    <p class="mt-1 text-sm text-slate-600">Use o leitor de código de barras (EAN) ou busque pelo nome do produto.</p>
                </div>

                <div class="px-6 py-6">
                    <form class="grid gap-4" method="post" action="{{ route('admin.inventory.movements.store') }}">
                        @csrf

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Loja</span>
                            <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id" required>
                                @foreach ($stores as $s)
                                    <option value="{{ $s->getKey() }}" @selected(old('store_id', optional($store)->getKey()) == $s->getKey())>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        @if ($product)
                            <input type="hidden" name="product_id" value="{{ $product->getKey() }}">
                            <div class="rounded-3xl border border-slate-200 bg-white p-4">
                                <div class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produto selecionado</div>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <a class="text-sm font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $product) }}">
                                        {{ $product->name }}
                                    </a>
                                    @if ($product->ean)
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                            EAN: <span class="font-mono">{{ $product->ean }}</span>
                                        </span>
                                    @endif
                                    @if ($product->sku)
                                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                            SKU: <span class="font-mono">{{ $product->sku }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                Nenhum produto selecionado ainda. Use a busca ao lado para localizar e escolher.
                            </div>
                        @endif

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Tipo</span>
                            <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="type" required>
                                <option value="in" @selected(old('type', 'in') === 'in')>Entrada</option>
                                <option value="out" @selected(old('type') === 'out')>Saída</option>
                                <option value="adjust" @selected(old('type') === 'adjust')>Ajuste (quantidade final)</option>
                            </select>
                        </label>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Quantidade</span>
                                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" min="0" step="1" name="quantity" value="{{ old('quantity', 1) }}" required>
                                <p class="text-xs text-slate-500">Para ajuste: informe a quantidade final.</p>
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Estoque mínimo (opcional)</span>
                                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" min="0" step="1" name="min_quantity" value="{{ old('min_quantity') }}">
                            </label>
                        </div>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Motivo (opcional)</span>
                            <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="reason" maxlength="120" list="reasons" value="{{ old('reason') }}" placeholder="Ex.: Compra, venda, inventário, avaria">
                            <datalist id="reasons">
                                <option value="Compra"></option>
                                <option value="Venda"></option>
                                <option value="Inventário"></option>
                                <option value="Avaria/Perda"></option>
                                <option value="Transferência"></option>
                            </datalist>
                        </label>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Observação (opcional)</span>
                            <textarea class="min-h-24 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="note" maxlength="2000" placeholder="Informações adicionais...">{{ old('note') }}</textarea>
                        </label>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Data/Hora (opcional)</span>
                            <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="datetime-local" name="occurred_at" value="{{ old('occurred_at') }}">
                        </label>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <button class="rounded-2xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 disabled:opacity-60" type="submit" @disabled(! $product)>
                                Registrar
                            </button>
                            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.inventory.index', ['store_id' => optional($store)->getKey()]) }}">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <aside class="lg:col-span-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight">Buscar produto</h2>
                <p class="mt-1 text-sm text-slate-600">Cole um EAN (somente números) ou busque pelo nome.</p>

                <form class="mt-4 grid gap-2" method="get" action="{{ route('admin.inventory.movements.create') }}">
                    <input type="hidden" name="store_id" value="{{ optional($store)->getKey() }}">
                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">EAN</span>
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="code" value="{{ $code }}" placeholder="Ex.: 7891234567890">
                    </label>

                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Nome / SKU</span>
                        <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="q" value="{{ $q }}" placeholder="Ex.: dipirona">
                    </label>

                    <button class="mt-2 h-12 rounded-2xl bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" type="submit">
                        Buscar
                    </button>
                </form>

                @if (! $product && $code !== '' && $matches->isEmpty())
                    <div class="mt-5 rounded-3xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Não encontrei produto para esse código.
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a class="rounded-2xl bg-brand-700 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.products.create', ['ean' => preg_replace('/\\D+/', '', $code)]) }}">
                                Cadastrar produto com EAN
                            </a>
                            <a class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.scanner', ['code' => $code]) }}">
                                Ir para Scanner
                            </a>
                        </div>
                    </div>
                @endif

                @if (! $product && $matches->isNotEmpty())
                    <div class="mt-5 grid gap-2">
                        <div class="text-xs font-semibold uppercase tracking-widest text-slate-500">Sugestões</div>
                        @foreach ($matches as $m)
                            <a class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 hover:bg-slate-100" href="{{ route('admin.inventory.movements.create', ['store_id' => optional($store)->getKey(), 'product_id' => $m->getKey()]) }}">
                                {{ $m->name }}
                                @if ($m->ean)
                                    <span class="ml-2 font-mono text-xs text-slate-500">{{ $m->ean }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
@endsection

