@extends('layouts.admin')

@section('title', 'Nova venda | Admin')
@section('heading', 'Nova venda')
@section('subtitle', 'Saída de mercadorias')

@section('content')
    <div
        class="grid gap-6 lg:grid-cols-12"
        data-line-items-form="1"
        data-api-url="{{ route('admin.api.products.search') }}"
        data-mode="sale"
        data-store-selector="select[name='store_id']"
    >
        <section class="lg:col-span-8">
            <form class="grid gap-6" action="{{ route('admin.sales.store') }}" method="post">
                @csrf

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Dados da venda</h2>
                        <p class="mt-1 text-sm text-slate-600">Loja, pagamento e observações.</p>
                    </div>

                    <div class="grid gap-4 px-6 py-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Loja</span>
                                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id" required>
                                    @foreach ($stores as $s)
                                        <option value="{{ $s->getKey() }}" @selected((int) old('store_id') === (int) $s->getKey())>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Pagamento (opcional)</span>
                                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="payment_method">
                                    <option value="">-</option>
                                    @foreach ($paymentMethods as $key => $label)
                                        <option value="{{ $key }}" @selected(old('payment_method') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Referência (opcional)</span>
                                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="reference" value="{{ old('reference') }}" placeholder="Cupom, pedido...">
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Data/Hora (opcional)</span>
                                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="datetime-local" name="occurred_at" value="{{ old('occurred_at') }}">
                            </label>
                        </div>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Observações (opcional)</span>
                            <textarea class="min-h-24 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="notes" maxlength="2000" placeholder="Ex.: desconto, troca, orientação...">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Itens</h2>
                        <p class="mt-1 text-sm text-slate-600">Escaneie os produtos e registre a quantidade.</p>
                    </div>

                    <div class="grid gap-4 px-6 py-6">
                        <div class="flex flex-wrap items-end gap-2">
                            <label class="grid flex-1 gap-2">
                                <span class="text-sm font-medium text-slate-700">Código (EAN/SKU)</span>
                                <input id="item-code" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" inputmode="numeric" autocomplete="off" placeholder="Escaneie aqui...">
                            </label>

                            <button id="add-code" class="h-12 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="button">
                                Adicionar
                            </button>

                            <a class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-50 grid place-items-center" href="{{ route('admin.scanner') }}">
                                Abrir Scanner
                            </a>
                        </div>

                        <div class="relative grid gap-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Buscar por nome</span>
                                <input id="product-search" class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" autocomplete="off" placeholder="Digite para pesquisar...">
                            </label>
                            <div id="search-results" class="ac-panel" data-open="0"></div>
                        </div>

                        <div id="items-empty" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700 {{ empty($items) ? '' : 'hidden' }}">
                            Nenhum item adicionado ainda. Use o campo <span class="font-semibold">Código (EAN/SKU)</span> para começar.
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-white">
                                    <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                        <th class="px-4 py-3">Produto</th>
                                        <th class="px-4 py-3">Quantidade</th>
                                        <th class="px-4 py-3">Preço (un.)</th>
                                        <th class="px-4 py-3 text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="items-body" data-next-index="{{ is_array($items) ? count($items) : 0 }}" class="divide-y divide-slate-200">
                                    @foreach ($items as $idx => $item)
                                        @php
                                            $productId = (int) ($item['product_id'] ?? 0);
                                            $product = $productId > 0 ? ($productsById->get($productId) ?? null) : null;
                                            $quantity = (int) ($item['quantity'] ?? 1);
                                            $unitPrice = $item['unit_price'] ?? ($product?->price ?? '');
                                            $unitPrice = $unitPrice === null ? '' : $unitPrice;
                                        @endphp
                                        @if ($productId > 0)
                                            <tr data-item-row="1" data-product-id="{{ $productId }}">
                                                <td class="px-4 py-4">
                                                    <div class="font-semibold text-slate-900">{{ $product?->name ?? 'Produto #' . $productId }}</div>
                                                    <div class="mt-1 text-xs text-slate-500">
                                                        @if ($product?->ean)
                                                            EAN: <span class="font-mono">{{ $product->ean }}</span>
                                                        @endif
                                                        @if ($product?->ean && $product?->sku)
                                                            <span class="mx-2 text-slate-300">|</span>
                                                        @endif
                                                        @if ($product?->sku)
                                                            SKU: <span class="font-mono">{{ $product->sku }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="mt-2 text-xs text-slate-600" data-item-stock="1"></div>
                                                    <div class="mt-1 text-xs text-slate-600" data-item-margin="1"></div>
                                                    <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $productId }}">
                                                </td>
                                                <td class="px-4 py-4">
                                                    <input class="h-11 w-28 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" name="items[{{ $idx }}][quantity]" min="1" step="1" value="{{ $quantity }}" required>
                                                </td>
                                                <td class="px-4 py-4">
                                                    <input class="h-11 w-32 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" name="items[{{ $idx }}][unit_price]" min="0" step="0.01" value="{{ $unitPrice }}" placeholder="0,00">
                                                </td>
                                                <td class="px-4 py-4">
                                                    <div class="flex justify-end">
                                                        <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="button" data-remove-item="1">
                                                            Remover
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div id="items-error" class="hidden rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"></div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" type="submit">
                        Registrar venda
                    </button>
                    <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.sales.index') }}">
                        Cancelar
                    </a>
                </div>
            </form>
        </section>

        <aside class="grid gap-4 lg:col-span-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" data-items-summary="1">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Resumo</p>
                <div class="mt-4 grid gap-3 text-sm text-slate-700">
                    <div class="flex items-center justify-between">
                        <span>Itens</span>
                        <span class="font-semibold" data-summary-qty="1">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Total</span>
                        <span class="font-semibold text-slate-900" data-summary-total="1">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Lucro estimado</span>
                        <span class="font-semibold" data-summary-profit="1">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Margem</span>
                        <span class="font-semibold" data-summary-margin="1">-</span>
                    </div>
                </div>
                <p class="mt-4 text-xs text-slate-500">Calculado com base no último custo por loja.</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight">Atenção</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-700">
                    <li>O sistema registra a saída no Estoque automaticamente.</li>
                    <li>Se o estoque não for suficiente, a venda será bloqueada.</li>
                    <li>Use o Scanner para cadastrar produtos rapidamente.</li>
                </ul>
            </div>
        </aside>
    </div>

    {{--
    <script>
        (function () {
            const apiUrl = @json(route('admin.api.products.search'));

            const storeSelect = document.querySelector('select[name="store_id"]');
            const codeInput = document.getElementById('item-code');
            const addCodeBtn = document.getElementById('add-code');
            const emptyState = document.getElementById('items-empty');
            const tbody = document.getElementById('items-body');
            const errorBox = document.getElementById('items-error');

            const searchInput = document.getElementById('product-search');
            const searchResults = document.getElementById('search-results');

            function esc(v) {
                return String(v ?? '').replace(/[&<>"']/g, (ch) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[ch]));
            }

            function showError(msg) {
                if (!errorBox) return;
                errorBox.textContent = msg;
                errorBox.classList.remove('hidden');
            }

            function clearError() {
                if (!errorBox) return;
                errorBox.textContent = '';
                errorBox.classList.add('hidden');
            }

            function refreshEmpty() {
                if (!emptyState || !tbody) return;
                const hasRows = tbody.querySelector('[data-item-row="1"]') !== null;
                emptyState.classList.toggle('hidden', hasRows);
            }

            function nextIndex() {
                const n = parseInt(tbody?.dataset?.nextIndex || '0', 10) || 0;
                if (tbody) tbody.dataset.nextIndex = String(n + 1);
                return n;
            }

            function findRowByProductId(productId) {
                return tbody?.querySelector('[data-item-row="1"][data-product-id="' + String(productId) + '"]') || null;
            }

            const moneyFmt = (() => {
                try {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
                } catch (e) {
                    return null;
                }
            })();

            function fmtMoney(n) {
                if (n === null || n === undefined || !Number.isFinite(n)) return '-';
                if (moneyFmt) return moneyFmt.format(n);
                return 'R$ ' + n.toFixed(2).replace('.', ',');
            }

            function toNumber(v) {
                const s = String(v ?? '').trim().replace(',', '.');
                const n = parseFloat(s);
                return Number.isFinite(n) ? n : null;
            }

            function currentStoreId() {
                const v = storeSelect ? String(storeSelect.value || '').trim() : '';
                return v !== '' ? v : null;
            }

            function buildApiUrl(params) {
                const usp = new URLSearchParams(params || {});
                const sid = currentStoreId();
                if (sid) usp.set('store_id', sid);
                return apiUrl + '?' + usp.toString();
            }

            function updateRowMetrics(tr, product) {
                if (!tr) return;
                const stockEl = tr.querySelector('[data-item-stock="1"]');
                const marginEl = tr.querySelector('[data-item-margin="1"]');
                const qtyInput = tr.querySelector('input[name$="[quantity]"]');
                const priceInput = tr.querySelector('input[name$="[unit_price]"]');

                const stock = product && product.stock !== null && product.stock !== undefined
                    ? parseInt(product.stock, 10) || 0
                    : null;
                const otherStores = Array.isArray(product?.stock_other_stores) ? product.stock_other_stores : [];

                const qty = qtyInput ? (parseInt(String(qtyInput.value || '0'), 10) || 0) : 0;

                if (stockEl) {
                    let stockText = stock === null ? 'Estoque: -' : `Estoque nesta loja: ${stock}`;
                    if (stock !== null && qty > 0 && qty > stock) {
                        stockText += ` (insuficiente para ${qty})`;
                        stockEl.className = 'mt-2 text-xs font-semibold text-rose-800';
                    } else if (stock === 0) {
                        stockEl.className = 'mt-2 text-xs font-semibold text-amber-800';
                    } else {
                        stockEl.className = 'mt-2 text-xs text-slate-600';
                    }

                    if (otherStores.length) {
                        const top = otherStores.slice(0, 2).map((s) => `${s.name} (${s.quantity})`).join(', ');
                        const rest = otherStores.length > 2 ? ` +${otherStores.length - 2}` : '';
                        stockText += ` · Outras lojas: ${top}${rest}`;
                    }

                    stockEl.textContent = stockText;
                }

                if (marginEl) {
                    const cost = toNumber(product?.last_unit_cost);
                    const unitPrice = toNumber(priceInput ? priceInput.value : product?.price);

                    const costTxt = cost !== null ? fmtMoney(cost) : '-';

                    if (cost === null || unitPrice === null || unitPrice <= 0) {
                        marginEl.className = 'mt-1 text-xs text-slate-600';
                        marginEl.textContent = `Custo (último): ${costTxt} · Margem: -`;
                        return;
                    }

                    const gross = unitPrice - cost;
                    const pct = (gross / unitPrice) * 100;

                    marginEl.className = 'mt-1 text-xs ' + (gross < 0 ? 'font-semibold text-rose-800' : 'text-slate-600');
                    marginEl.textContent = `Custo (último): ${costTxt} · Margem: ${pct.toFixed(1)}% (${fmtMoney(gross)})`;
                }
            }

            function addProductRow(product) {
                if (!tbody) return;
                clearError();

                const existing = findRowByProductId(product.id);
                if (existing) {
                    const qtyInput = existing.querySelector('input[name$="[quantity]"]');
                    if (qtyInput) qtyInput.value = String((parseInt(qtyInput.value || '0', 10) || 0) + 1);
                    refreshEmpty();
                    return;
                }

                const idx = nextIndex();
                const defaultPrice = product.price !== null && product.price !== undefined ? String(product.price) : '';

                const tr = document.createElement('tr');
                tr.setAttribute('data-item-row', '1');
                tr.setAttribute('data-product-id', String(product.id));
                tr.innerHTML = `
                    <td class="px-4 py-4">
                        <div class="font-semibold text-slate-900">${esc(product.name)}</div>
                        <div class="mt-1 text-xs text-slate-500">
                            ${product.ean ? `EAN: <span class="font-mono">${esc(product.ean)}</span>` : ''}
                            ${product.ean && product.sku ? `<span class="mx-2 text-slate-300">|</span>` : ''}
                            ${product.sku ? `SKU: <span class="font-mono">${esc(product.sku)}</span>` : ''}
                        </div>
                        <div class="mt-2 text-xs text-slate-600" data-item-stock="1"></div>
                        <div class="mt-1 text-xs text-slate-600" data-item-margin="1"></div>
                        <input type="hidden" name="items[${idx}][product_id]" value="${esc(product.id)}">
                    </td>
                    <td class="px-4 py-4">
                        <input class="h-11 w-28 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" name="items[${idx}][quantity]" min="1" step="1" value="1" required>
                    </td>
                    <td class="px-4 py-4">
                        <input class="h-11 w-32 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="number" name="items[${idx}][unit_price]" min="0" step="0.01" value="${esc(defaultPrice)}" placeholder="0,00">
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex justify-end">
                            <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="button" data-remove-item="1">Remover</button>
                        </div>
                    </td>
                `;

                tbody.appendChild(tr);
                updateRowMetrics(tr, product);

                const qtyInput = tr.querySelector('input[name$="[quantity]"]');
                const priceInput = tr.querySelector('input[name$="[unit_price]"]');
                if (qtyInput) qtyInput.addEventListener('input', () => updateRowMetrics(tr, product));
                if (priceInput) priceInput.addEventListener('input', () => updateRowMetrics(tr, product));
                refreshEmpty();
            }

            async function lookupByCode(code) {
                const res = await fetch(buildApiUrl({ code }), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok || !data || !data.ok || !data.product) return null;
                return data.product;
            }

            async function searchByName(q) {
                const res = await fetch(buildApiUrl({ q, limit: '8' }), {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok || !data || !data.ok || !Array.isArray(data.products)) return [];
                return data.products;
            }

            async function handleAddCode() {
                if (!codeInput) return;
                const code = String(codeInput.value || '').trim();
                if (!code) return;

                addCodeBtn.disabled = true;
                try {
                    const product = await lookupByCode(code);
                    if (!product) {
                        showError('Produto não encontrado para este código.');
                        return;
                    }
                    addProductRow(product);
                    codeInput.value = '';
                    codeInput.focus();
                } catch (e) {
                    showError('Falha ao buscar produto. Verifique a conexão.');
                } finally {
                    addCodeBtn.disabled = false;
                }
            }

            let searchTimer = null;
            async function handleSearchChange() {
                if (!searchInput || !searchResults) return;
                const q = String(searchInput.value || '').trim();
                if (q.length < 3) {
                    searchResults.classList.add('hidden');
                    searchResults.innerHTML = '';
                    return;
                }

                if (searchTimer) clearTimeout(searchTimer);
                searchTimer = setTimeout(async () => {
                    const products = await searchByName(q);

                    searchResults.innerHTML = '';
                    if (!products.length) {
                        const empty = document.createElement('div');
                        empty.className = 'px-4 py-3 text-sm text-slate-600';
                        empty.textContent = 'Nenhum produto encontrado.';
                        searchResults.appendChild(empty);
                        searchResults.classList.remove('hidden');
                        return;
                    }

                    products.forEach((p) => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full border-t border-slate-200 px-4 py-3 text-left text-sm hover:bg-slate-50';
                        btn.innerHTML = `<div class="font-semibold text-slate-900">${esc(p.name)}</div>
                            <div class="mt-1 text-xs text-slate-500">
                                ${p.ean ? `EAN: <span class="font-mono">${esc(p.ean)}</span>` : ''}
                                ${p.ean && p.sku ? `<span class="mx-2 text-slate-300">|</span>` : ''}
                                ${p.sku ? `SKU: <span class="font-mono">${esc(p.sku)}</span>` : ''}
                            </div>
                            <div class="mt-1 text-xs text-slate-500">
                                ${p.price ? `Preço: <span class="font-semibold text-slate-700">${esc(p.price)}</span>` : 'Preço: -'}
                                ${p.stock !== null && p.stock !== undefined ? `<span class="mx-2 text-slate-300">|</span>Estoque: <span class="font-semibold text-slate-700">${esc(p.stock)}</span>` : ''}
                            </div>`;
                        btn.addEventListener('click', () => {
                            addProductRow(p);
                            searchResults.classList.add('hidden');
                            searchResults.innerHTML = '';
                            searchInput.value = '';
                            codeInput?.focus();
                        });
                        searchResults.appendChild(btn);
                    });

                    searchResults.classList.remove('hidden');
                }, 250);
            }

            if (codeInput) {
                codeInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        handleAddCode();
                    }
                });
            }

            if (addCodeBtn) addCodeBtn.addEventListener('click', handleAddCode);

            if (tbody) {
                tbody.addEventListener('click', (e) => {
                    const target = e.target;
                    if (!target || !target.matches || !target.matches('[data-remove-item="1"]')) return;
                    const row = target.closest('tr');
                    if (row) row.remove();
                    refreshEmpty();
                });
            }

            if (searchInput) searchInput.addEventListener('input', handleSearchChange);

            refreshEmpty();
            codeInput?.focus();
        })();
    </script>
    --}}
@endsection
