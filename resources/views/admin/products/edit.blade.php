@extends('layouts.admin')

@section('title', 'Editar produto | Admin')
@section('heading', 'Editar produto')
@section('subtitle', $product->name)

@section('content')
    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.products.update', $product) }}" method="post">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nome</label>
                <input id="product-name" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="name" value="{{ old('name', $product->name) }}" required>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Slug</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="slug" value="{{ old('slug', $product->slug) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">SKU</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="sku" value="{{ old('sku', $product->sku) }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">EAN</label>
                <div class="flex items-center gap-2">
                    <input id="product-ean" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="ean" value="{{ old('ean', $product->ean) }}">
                    <button id="gtin-lookup-btn" class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 disabled:opacity-60" type="button">
                        Buscar
                    </button>
                </div>
                <p id="gtin-lookup-status" class="text-xs text-slate-500"></p>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Preço (opcional)</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}">
            </div>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Fornecedor (opcional)</label>
            <select class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="supplier_id">
                <option value="">-</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->getKey() }}" @selected((int) old('supplier_id', $product->supplier_id) === (int) $supplier->getKey())>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Descrição curta</label>
            <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="short_description" maxlength="500" value="{{ old('short_description', $product->short_description) }}">
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Descrição</label>
            <textarea class="min-h-28 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="description">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Imagem (URL)</label>
            <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="image_url" value="{{ old('image_url', $product->image_url) }}">
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                <span class="font-semibold text-slate-800">Ativo</span>
            </label>
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                <span class="font-semibold text-slate-800">Destaque</span>
            </label>
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="requires_prescription" value="1" @checked(old('requires_prescription', $product->requires_prescription))>
                <span class="font-semibold text-slate-800">Requer receita</span>
            </label>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Categorias</label>
            @php
                $selectedIds = old('category_ids', $product->categories->pluck('id')->all());
            @endphp
            <select class="min-h-24 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="category_ids[]" multiple>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedIds, true))>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500">Segure Ctrl/Command para selecionar várias.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">
                Atualizar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.products.index') }}">Voltar</a>
            <a class="ml-auto rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.products.show', $product) }}">
                Ver
            </a>
        </div>
    </form>

    <script>
        (function () {
            const endpoint = @json(route('admin.api.gtin.lookup'));
            const eanInput = document.getElementById('product-ean');
            const nameInput = document.getElementById('product-name');
            const btn = document.getElementById('gtin-lookup-btn');
            const status = document.getElementById('gtin-lookup-status');
            const shortInput = document.querySelector('[name="short_description"]');

            if (!eanInput || !nameInput || !btn || !status) return;

            function setStatus(text, kind) {
                status.textContent = text || '';
                status.className = 'text-xs ' + (kind === 'ok'
                    ? 'text-emerald-700'
                    : kind === 'err'
                        ? 'text-rose-700'
                        : 'text-slate-500');
            }

            async function lookup() {
                const code = (eanInput.value || '').trim();
                const digits = code.replace(/\\D+/g, '');
                if (digits.length < 8) {
                    setStatus('Informe um EAN/GTIN válido para buscar.', 'err');
                    return;
                }

                btn.disabled = true;
                setStatus('Buscando dados...', 'info');

                try {
                    const url = new URL(endpoint);
                    url.searchParams.set('code', code);

                    const res = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    const body = await res.json().catch(() => null);
                    if (!res.ok || !body || !body.ok || !body.data) {
                        setStatus((body && body.message) ? body.message : 'Não consegui buscar dados para esse código.', 'err');
                        return;
                    }

                    const data = body.data;

                    if ((nameInput.value || '').trim() === '' && data.name) {
                        nameInput.value = data.name;
                    }

                    if (shortInput && (shortInput.value || '').trim() === '' && (data.size || data.brand)) {
                        const extra = [data.brand, data.size].filter(Boolean).join(' - ');
                        shortInput.value = extra;
                    }

                    setStatus('Dados encontrados. Revise e salve.', 'ok');
                } catch (e) {
                    setStatus('Falha ao consultar base externa. Tente novamente.', 'err');
                } finally {
                    btn.disabled = false;
                }
            }

            btn.addEventListener('click', lookup);
        })();
    </script>
@endsection
