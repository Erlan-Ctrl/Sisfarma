// Shared, lightweight "line items" UI used by purchase/sale/transfer forms.
// Keeps Blade templates simple and avoids duplicating large inline scripts.

function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, (ch) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[ch]));
}

const moneyFmt = (() => {
    try {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    } catch {
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

function toInt(v) {
    const n = parseInt(String(v ?? '').trim(), 10);
    return Number.isFinite(n) ? n : 0;
}

/**
 * @param {HTMLElement} root
 */
function initLineItemsForm(root) {
    const apiUrl = String(root.dataset.apiUrl || '').trim();
    const mode = String(root.dataset.mode || '').trim(); // sale|purchase|transfer
    const storeSelector = String(root.dataset.storeSelector || '').trim();
    const hasStockColumn = String(root.dataset.stockColumn || '') === '1';

    if (!apiUrl || !mode) return;

    const storeEl = storeSelector ? document.querySelector(storeSelector) : null;

    const codeInput = root.querySelector('#item-code');
    const addCodeBtn = root.querySelector('#add-code');
    const emptyState = root.querySelector('#items-empty');
    const tbody = root.querySelector('#items-body');
    const errorBox = root.querySelector('#items-error');

    const searchInput = root.querySelector('#product-search');
    const searchResults = root.querySelector('#search-results');

    const summaryEl = root.querySelector('[data-items-summary="1"]');

    if (!tbody) return;

    const formEl = tbody.closest('form');

    function currentStoreId() {
        if (!storeEl) return null;
        const v = String(storeEl.value || '').trim();
        return v !== '' ? v : null;
    }

    function buildApiUrl(params) {
        const usp = new URLSearchParams(params || {});
        const sid = currentStoreId();
        if (sid) usp.set('store_id', sid);
        return apiUrl + '?' + usp.toString();
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
        if (!emptyState) return;
        const hasRows = tbody.querySelector('[data-item-row="1"]') !== null;
        emptyState.classList.toggle('hidden', hasRows);
    }

    function nextIndex() {
        const n = parseInt(tbody?.dataset?.nextIndex || '0', 10) || 0;
        tbody.dataset.nextIndex = String(n + 1);
        return n;
    }

    function findRowByProductId(productId) {
        return tbody.querySelector('[data-item-row="1"][data-product-id="' + String(productId) + '"]');
    }

    function setRowProduct(tr, product) {
        try {
            tr.dataset.productJson = JSON.stringify(product || {});
        } catch {
            tr.dataset.productJson = '{}';
        }
    }

    function getRowProduct(tr) {
        const raw = String(tr?.dataset?.productJson || '{}');
        try {
            const decoded = JSON.parse(raw);
            return decoded && typeof decoded === 'object' ? decoded : {};
        } catch {
            return {};
        }
    }

    function updateRowMetrics(tr) {
        const product = getRowProduct(tr);

        const qtyInput = tr.querySelector('input[name$="[quantity]"]');
        const qty = qtyInput ? toInt(qtyInput.value) : 0;

        const stockEl = tr.querySelector('[data-item-stock="1"]');
        const metaEl = tr.querySelector('[data-item-margin="1"]');
        const stockColEl = hasStockColumn ? tr.querySelector('[data-item-stock-col="1"]') : null;

        const stock = product && product.stock !== null && product.stock !== undefined
            ? toInt(product.stock)
            : null;
        const otherStores = Array.isArray(product?.stock_other_stores) ? product.stock_other_stores : [];

        // Stock display (sale/transfer/purchase).
        if (stockColEl) {
            stockColEl.textContent = stock === null ? '-' : String(stock);
            stockColEl.className = 'px-4 py-4 text-sm font-semibold ' + (stock !== null && qty > stock ? 'text-rose-800' : 'text-slate-700');
        }

        if (stockEl) {
            let stockText = stock === null ? 'Estoque: -' : (mode === 'purchase' ? `Estoque atual: ${stock}` : `Estoque nesta loja: ${stock}`);
            if (stock !== null && qty > 0 && qty > stock && (mode === 'sale' || mode === 'transfer')) {
                stockText += ` (insuficiente para ${qty})`;
                stockEl.className = 'mt-2 text-xs font-semibold text-rose-800';
            } else if (stock === 0 && (mode === 'sale' || mode === 'transfer')) {
                stockEl.className = 'mt-2 text-xs font-semibold text-amber-800';
            } else {
                stockEl.className = 'mt-2 text-xs text-slate-600';
            }

            if (otherStores.length && (mode === 'sale' || mode === 'transfer')) {
                const top = otherStores.slice(0, 2).map((s) => `${s.name} (${s.quantity})`).join(', ');
                const rest = otherStores.length > 2 ? ` +${otherStores.length - 2}` : '';
                stockText += ` · Outras lojas: ${top}${rest}`;
            }

            stockEl.textContent = stockText;
        }

        // Meta line (margin for sale; last cost hint for others).
        if (!metaEl) return;

        const cost = toNumber(product?.last_unit_cost);
        const costTxt = cost !== null ? fmtMoney(cost) : '-';

        if (mode === 'sale') {
            const priceInput = tr.querySelector('input[name$="[unit_price]"]');
            const unitPrice = toNumber(priceInput ? priceInput.value : product?.price);

            if (cost === null || unitPrice === null || unitPrice <= 0) {
                metaEl.className = 'mt-1 text-xs text-slate-600';
                metaEl.textContent = `Custo (último): ${costTxt} · Margem: -`;
                return;
            }

            const gross = unitPrice - cost;
            const pct = (gross / unitPrice) * 100;

            metaEl.className = 'mt-1 text-xs ' + (gross < 0 ? 'font-semibold text-rose-800' : 'text-slate-600');
            metaEl.textContent = `Custo (último): ${costTxt} · Margem: ${pct.toFixed(1)}% (${fmtMoney(gross)})`;
            return;
        }

        if (mode === 'purchase') {
            metaEl.className = 'mt-1 text-xs text-slate-600';
            metaEl.textContent = `Custo (último): ${costTxt}`;
            return;
        }

        // transfer
        metaEl.className = 'mt-1 text-xs text-slate-600';
        metaEl.textContent = cost !== null ? `Custo (último): ${costTxt}` : '';
    }

    function refreshSummary() {
        if (!summaryEl) return;

        const rows = Array.from(tbody.querySelectorAll('[data-item-row="1"]'));

        let totalQty = 0;
        let totalAmount = 0;
        let totalCost = 0;
        let hasAnyCost = false;

        rows.forEach((tr) => {
            const p = getRowProduct(tr);
            const qty = toInt(tr.querySelector('input[name$="[quantity]"]')?.value);
            totalQty += Math.max(0, qty);

            if (mode === 'sale') {
                const unitPrice = toNumber(tr.querySelector('input[name$="[unit_price]"]')?.value) ?? 0;
                totalAmount += qty * unitPrice;

                const unitCost = toNumber(p?.last_unit_cost);
                if (unitCost !== null) {
                    totalCost += qty * unitCost;
                    hasAnyCost = true;
                }
            } else if (mode === 'purchase') {
                const unitCost = toNumber(tr.querySelector('input[name$="[unit_cost]"]')?.value) ?? 0;
                totalAmount += qty * unitCost;
            }
        });

        const qtyEl = summaryEl.querySelector('[data-summary-qty="1"]');
        const totalEl = summaryEl.querySelector('[data-summary-total="1"]');
        const profitEl = summaryEl.querySelector('[data-summary-profit="1"]');
        const marginEl = summaryEl.querySelector('[data-summary-margin="1"]');

        if (qtyEl) qtyEl.textContent = String(totalQty);
        if (totalEl) totalEl.textContent = fmtMoney(totalAmount);

        if (mode === 'sale') {
            const profit = totalAmount - totalCost;
            if (profitEl) profitEl.textContent = hasAnyCost ? fmtMoney(profit) : '-';
            if (marginEl) {
                if (!hasAnyCost || totalAmount <= 0) {
                    marginEl.textContent = '-';
                } else {
                    marginEl.textContent = ((profit / totalAmount) * 100).toFixed(1) + '%';
                }
            }
        } else {
            if (profitEl) profitEl.textContent = '';
            if (marginEl) marginEl.textContent = '';
        }
    }

    async function fetchJson(url, options) {
        const opts = options && typeof options === 'object' ? options : {};
        const headers = { Accept: 'application/json', ...(opts.headers || {}) };
        const res = await fetch(url, { ...opts, headers });
        const data = await res.json().catch(() => null);
        if (!res.ok) return { ok: false, data };
        return { ok: true, data };
    }

    // Small in-memory cache to keep the checkout flow snappy.
    // Stock/cost depends on store, so cache keys include current store_id.
    const cacheTtlMs = 15_000;
    const apiCache = new Map();

    function cacheKey(prefix, value) {
        const sid = currentStoreId() ?? '0';
        return `${prefix}:${sid}:${String(value ?? '')}`;
    }

    function cacheGet(key) {
        const entry = apiCache.get(key);
        if (!entry) return null;
        if (Date.now() - entry.t > cacheTtlMs) {
            apiCache.delete(key);
            return null;
        }
        return entry.v;
    }

    function cacheSet(key, value) {
        if (apiCache.size > 250) apiCache.clear();
        apiCache.set(key, { t: Date.now(), v: value });
    }

    // Abort prior name searches to avoid "slow network" stacking.
    let searchAbort = null;

    async function lookupByCode(code) {
        const normalized = String(code ?? '').trim().replace(/\s+/g, '').toUpperCase();
        const key = cacheKey('code', normalized);
        const cached = cacheGet(key);
        if (cached !== null) return cached;

        const { ok, data } = await fetchJson(buildApiUrl({ code }));
        const product = ok && data && data.ok && data.product ? data.product : null;

        cacheSet(key, product);
        if (product?.id) cacheSet(cacheKey('id', product.id), product);

        return product;
    }

    async function lookupById(id) {
        const key = cacheKey('id', id);
        const cached = cacheGet(key);
        if (cached !== null) return cached;

        const { ok, data } = await fetchJson(buildApiUrl({ id: String(id) }));
        const product = ok && data && data.ok && data.product ? data.product : null;

        cacheSet(key, product);
        return product;
    }

    async function searchByName(q) {
        const normalized = String(q ?? '').trim().toLowerCase();
        const key = cacheKey('q', normalized);
        const cached = cacheGet(key);
        if (cached !== null) return cached;

        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();

        try {
            const { ok, data } = await fetchJson(buildApiUrl({ q, limit: '8' }), { signal: searchAbort.signal });
            const products = ok && data && data.ok && Array.isArray(data.products) ? data.products : [];
            cacheSet(key, products);
            products.forEach((p) => {
                if (p?.id) cacheSet(cacheKey('id', p.id), p);
            });
            return products;
        } catch (err) {
            if (err && typeof err === 'object' && err.name === 'AbortError') return [];
            return [];
        }
    }

    function addProductRow(product) {
        clearError();

        const existing = findRowByProductId(product.id);
        if (existing) {
            const qtyInput = existing.querySelector('input[name$="[quantity]"]');
            if (qtyInput) qtyInput.value = String(toInt(qtyInput.value) + 1);
            setRowProduct(existing, product);
            updateRowMetrics(existing);
            refreshSummary();
            refreshEmpty();
            return;
        }

        const idx = nextIndex();
        const tr = document.createElement('tr');
        tr.setAttribute('data-item-row', '1');
        tr.setAttribute('data-product-id', String(product.id));

        const nameHtml = `
            <div class="font-semibold text-slate-900">${esc(product.name)}</div>
            <div class="mt-1 text-xs text-slate-500">
                ${product.ean ? `EAN: <span class="font-mono">${esc(product.ean)}</span>` : ''}
                ${product.ean && product.sku ? `<span class="mx-2 text-slate-300">|</span>` : ''}
                ${product.sku ? `SKU: <span class="font-mono">${esc(product.sku)}</span>` : ''}
            </div>
            <div class="mt-2 text-xs text-slate-600" data-item-stock="1"></div>
            <div class="mt-1 text-xs text-slate-600" data-item-margin="1"></div>
            <input type="hidden" name="items[${idx}][product_id]" value="${esc(product.id)}">
        `;

        if (mode === 'sale') {
            const defaultPrice = product.price !== null && product.price !== undefined ? String(product.price) : '';
            tr.innerHTML = `
                <td class="px-4 py-4">${nameHtml}</td>
                <td class="px-4 py-4">
                    <input class="h-11 w-28 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        type="number" name="items[${idx}][quantity]" min="1" step="1" value="1" required>
                </td>
                <td class="px-4 py-4">
                    <input class="h-11 w-32 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        type="number" name="items[${idx}][unit_price]" min="0" step="0.01" value="${esc(defaultPrice)}" placeholder="0,00">
                </td>
                <td class="px-4 py-4">
                    <div class="flex justify-end">
                        <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100"
                            type="button" data-remove-item="1">Remover</button>
                    </div>
                </td>
            `;
        } else if (mode === 'purchase') {
            const defaultCost = product.last_unit_cost !== null && product.last_unit_cost !== undefined ? String(product.last_unit_cost) : '';
            tr.innerHTML = `
                <td class="px-4 py-4">${nameHtml}</td>
                <td class="px-4 py-4">
                    <input class="h-11 w-28 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        type="number" name="items[${idx}][quantity]" min="1" step="1" value="1" required>
                </td>
                <td class="px-4 py-4">
                    <input class="h-11 w-32 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        type="number" name="items[${idx}][unit_cost]" min="0" step="0.01" value="${esc(defaultCost)}" placeholder="0,00">
                </td>
                <td class="px-4 py-4">
                    <div class="flex justify-end">
                        <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100"
                            type="button" data-remove-item="1">Remover</button>
                    </div>
                </td>
            `;
        } else {
            // transfer
            tr.innerHTML = `
                <td class="px-4 py-4">${nameHtml}</td>
                <td class="px-4 py-4">
                    <input class="h-11 w-28 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                        type="number" name="items[${idx}][quantity]" min="1" step="1" value="1" required>
                </td>
                <td class="px-4 py-4" data-item-stock-col="1">-</td>
                <td class="px-4 py-4">
                    <div class="flex justify-end">
                        <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100"
                            type="button" data-remove-item="1">Remover</button>
                    </div>
                </td>
            `;
        }

        tbody.appendChild(tr);
        setRowProduct(tr, product);
        updateRowMetrics(tr);
        refreshSummary();
        refreshEmpty();
    }

    // Allow scanning multiple items back-to-back without waiting for the network.
    const addCodeBtnLabel = addCodeBtn ? String(addCodeBtn.textContent || '').trim() : '';
    const codeQueue = [];
    let processingCodes = false;

    function updateAddCodeButton() {
        if (!addCodeBtn || !addCodeBtnLabel) return;
        const n = codeQueue.length + (processingCodes ? 1 : 0);
        addCodeBtn.textContent = n > 0 ? `${addCodeBtnLabel} (${n})` : addCodeBtnLabel;
        if (n > 0) {
            addCodeBtn.setAttribute('aria-busy', 'true');
        } else {
            addCodeBtn.removeAttribute('aria-busy');
        }
    }

    async function processCodeQueue() {
        if (processingCodes) {
            updateAddCodeButton();
            return;
        }

        processingCodes = true;
        updateAddCodeButton();

        try {
            while (codeQueue.length) {
                const code = String(codeQueue.shift() || '').trim();
                if (!code) continue;
                updateAddCodeButton();

                const product = await lookupByCode(code);
                if (!product) {
                    showError(`Produto não encontrado: ${code}`);
                    continue;
                }

                addProductRow(product);
            }
        } catch {
            showError('Falha ao buscar produto. Verifique a conexão.');
        } finally {
            processingCodes = false;
            updateAddCodeButton();
        }
    }

    function handleAddCode() {
        if (!codeInput) return;
        const code = String(codeInput.value || '').trim();
        if (!code) return;

        clearError();
        codeInput.value = '';
        codeInput.focus();

        codeQueue.push(code);
        processCodeQueue();
    }

    let searchTimer = null;
    async function handleSearchChange() {
        if (!searchInput || !searchResults) return;
        const q = String(searchInput.value || '').trim();
        if (q.length < 3) {
            hideSearchResults();
            return;
        }

        if (searchTimer) clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            const query = q;
            const products = await searchByName(query);

            // Ignore stale responses.
            if (String(searchInput.value || '').trim() !== query) return;

            searchResults.innerHTML = '';
            const list = document.createElement('div');
            list.dataset.acList = '1';
            searchResults.appendChild(list);

            if (!products.length) {
                const empty = document.createElement('div');
                empty.className = 'ac-item';
                empty.innerHTML = `
                    <div>
                        <div class="ac-title">Nenhum produto encontrado</div>
                        <div class="ac-sub">Tente buscar por EAN, SKU ou parte do nome.</div>
                    </div>
                    <span class="ac-pill ac-pill--danger">0</span>
                `;
                list.appendChild(empty);
                showSearchResults();
                return;
            }

            products.forEach((p) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ac-item text-left';

                const price = p.price !== null && p.price !== undefined ? fmtMoney(toNumber(p.price) ?? null) : '-';
                const stockNum = p.stock !== null && p.stock !== undefined ? toInt(p.stock) : null;
                const stockText = stockNum === null ? '-' : String(stockNum);

                let pillText = price;
                let pillClass = 'ac-pill';
                if (mode === 'sale' || mode === 'transfer') {
                    if (stockNum !== null) {
                        pillText = `Estq. ${stockText}`;
                        if (stockNum <= 0) pillClass = 'ac-pill ac-pill--danger';
                    }
                }

                const subParts = [];
                if (p.ean) subParts.push(`EAN: <span class="font-mono">${esc(p.ean)}</span>`);
                if (p.sku) subParts.push(`SKU: <span class="font-mono">${esc(p.sku)}</span>`);
                if (mode === 'sale' || mode === 'transfer') subParts.push(`Preço: <span class="font-semibold">${esc(price)}</span>`);

                btn.innerHTML = `
                    <div class="min-w-0">
                        <div class="ac-title truncate">${esc(p.name)}</div>
                        ${subParts.length ? `<div class="ac-sub">${subParts.join(' <span class="mx-2 text-slate-300">|</span> ')}</div>` : ''}
                    </div>
                    <span class="${pillClass}">${pillText}</span>
                `;

                btn.addEventListener('click', () => {
                    // Fast path: add immediately; refresh with full payload in the background.
                    addProductRow(p);
                    hideSearchResults();
                    searchInput.value = '';
                    codeInput?.focus();

                    lookupById(p.id).then((full) => {
                        if (!full) return;
                        const row = findRowByProductId(full.id);
                        if (!row) return;
                        setRowProduct(row, full);
                        updateRowMetrics(row);
                        refreshSummary();
                    }).catch(() => {});
                });

                list.appendChild(btn);
            });

            showSearchResults();
        }, 250);
    }

    function isSearchResultsOpen() {
        if (!searchResults) return false;
        if (searchResults.dataset.open) return searchResults.dataset.open === '1';
        return !searchResults.classList.contains('hidden');
    }

    function showSearchResults() {
        if (!searchResults) return;
        searchResults.classList.remove('hidden');
        if (searchResults.classList.contains('ac-panel')) {
            searchResults.dataset.open = '1';
        }
    }

    function hideSearchResults() {
        if (!searchResults) return;
        if (searchResults.classList.contains('ac-panel')) {
            searchResults.dataset.open = '0';
        } else {
            searchResults.classList.add('hidden');
        }
        searchResults.innerHTML = '';
    }

    async function refreshAllRowsFromApi() {
        const rows = Array.from(tbody.querySelectorAll('[data-item-row="1"]'));
        if (!rows.length) return;

        clearError();

        await Promise.all(rows.map(async (tr) => {
            const pid = toInt(tr.dataset.productId);
            if (pid <= 0) return;
            const product = await lookupById(pid);
            if (!product) return;
            setRowProduct(tr, product);

            // If this is a newly-rendered server row, ensure placeholders exist.
            if (!tr.querySelector('[data-item-stock="1"]')) {
                const cell = tr.querySelector('td');
                if (cell) {
                    const stockDiv = document.createElement('div');
                    stockDiv.className = 'mt-2 text-xs text-slate-600';
                    stockDiv.setAttribute('data-item-stock', '1');
                    cell.appendChild(stockDiv);

                    const metaDiv = document.createElement('div');
                    metaDiv.className = 'mt-1 text-xs text-slate-600';
                    metaDiv.setAttribute('data-item-margin', '1');
                    cell.appendChild(metaDiv);
                }
            }

            if (hasStockColumn && !tr.querySelector('[data-item-stock-col="1"]')) {
                // Transfer server rows may have a plain <td> with "-".
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 3) {
                    tds[2].setAttribute('data-item-stock-col', '1');
                }
            }

            updateRowMetrics(tr);
        }));

        refreshSummary();
    }

    // Wire events
    if (codeInput) {
        codeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleAddCode();
            }
        });
    }
    if (addCodeBtn) addCodeBtn.addEventListener('click', handleAddCode);
    if (searchInput) searchInput.addEventListener('input', handleSearchChange);
    if (searchInput) {
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideSearchResults();
                searchInput.blur();
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (!searchResults || !isSearchResultsOpen()) return;
        const target = e.target;
        if (!(target instanceof Node)) return;
        if (searchResults.contains(target) || searchInput?.contains(target)) return;
        hideSearchResults();
    });

    tbody.addEventListener('click', (e) => {
        const target = e.target;
        if (!target || !target.matches) return;
        if (!target.matches('[data-remove-item="1"]')) return;
        const row = target.closest('tr');
        if (row) row.remove();
        refreshEmpty();
        refreshSummary();
    });

    tbody.addEventListener('input', (e) => {
        const target = e.target;
        if (!target || !target.matches) return;
        if (!target.matches('input')) return;
        const tr = target.closest('tr');
        if (!tr) return;
        if (!tr.matches('[data-item-row="1"]')) return;
        updateRowMetrics(tr);
        refreshSummary();
    });

    if (storeEl) {
        storeEl.addEventListener('change', () => {
            refreshAllRowsFromApi();
        });
    }

    if (formEl) {
        formEl.addEventListener('submit', (e) => {
            const rows = Array.from(tbody.querySelectorAll('[data-item-row="1"]'));
            if (!rows.length) {
                showError('Adicione ao menos 1 item para continuar.');
                e.preventDefault();
                return;
            }

            if (mode !== 'sale' && mode !== 'transfer') return;

            const problems = [];
            rows.forEach((tr) => {
                const p = getRowProduct(tr);
                const stock = p && p.stock !== null && p.stock !== undefined ? toInt(p.stock) : null;
                if (stock === null) return;
                const qty = toInt(tr.querySelector('input[name$="[quantity]"]')?.value);
                if (qty > stock) {
                    problems.push(`${p?.name || 'Produto #' + String(tr.dataset.productId)} (${qty} > ${stock})`);
                }
            });

            if (problems.length) {
                showError('Estoque insuficiente para: ' + problems.slice(0, 4).join(', ') + (problems.length > 4 ? '...' : ''));
                e.preventDefault();
            }
        });
    }

    // Initial paint for prefilled rows
    refreshEmpty();
    refreshAllRowsFromApi();
    refreshSummary();
    codeInput?.focus();
}

export function initLineItemsForms() {
    document.querySelectorAll('[data-line-items-form="1"]').forEach((el) => {
        if (!(el instanceof HTMLElement)) return;
        initLineItemsForm(el);
    });
}
