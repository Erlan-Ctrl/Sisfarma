function prefersReducedMotion() {
    try {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch {
        return false;
    }
}

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

function fmtMoney(v) {
    const n = Number.parseFloat(String(v ?? '').replace(',', '.'));
    if (!Number.isFinite(n)) return '-';
    if (moneyFmt) return moneyFmt.format(n);
    return 'R$ ' + n.toFixed(2).replace('.', ',');
}

function debounce(fn, waitMs) {
    let t = 0;
    return (...args) => {
        if (t) window.clearTimeout(t);
        t = window.setTimeout(() => fn(...args), waitMs);
    };
}

function setPanelOpen(panel, input, isOpen) {
    if (!(panel instanceof HTMLElement)) return;
    panel.dataset.open = isOpen ? '1' : '0';
    if (input instanceof HTMLElement) {
        input.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
}

export function initHeaderProductSearchAutocomplete() {
    const form = document.querySelector('form[data-product-autocomplete="1"]');
    if (!(form instanceof HTMLFormElement)) return;

    const apiUrl = String(form.dataset.apiUrl || '').trim();
    if (!apiUrl) return;

    const minChars = Number.parseInt(form.dataset.minChars || '2', 10);
    const limit = Number.parseInt(form.dataset.limit || '8', 10);

    const input = form.querySelector('input[name="q"]');
    if (!(input instanceof HTMLInputElement)) return;

    const wrapper = form.querySelector('[data-autocomplete-wrapper="1"]');
    if (!(wrapper instanceof HTMLElement)) return;

    const panel = document.createElement('div');
    panel.className = 'ac-panel';
    panel.dataset.open = '0';
    panel.setAttribute('role', 'listbox');
    panel.setAttribute('aria-label', 'Sugestoes de produtos');

    const panelId = 'ac-' + Math.random().toString(16).slice(2);
    panel.id = panelId;

    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', panelId);
    input.setAttribute('autocomplete', 'off');

    const footer = document.createElement('div');
    footer.className = 'ac-footer';
    footer.innerHTML = '<span>Enter para pesquisar</span><span class="font-mono">Esc</span>';

    const list = document.createElement('div');
    list.dataset.acList = '1';

    panel.appendChild(list);
    panel.appendChild(footer);
    wrapper.appendChild(panel);

    let items = [];
    let activeIndex = -1;
    let abort = null;
    let isLoading = false;
    let usedArrows = false;

    function submitForm() {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    function setActiveIndex(next) {
        activeIndex = next;
        const buttons = Array.from(list.querySelectorAll('button[data-ac-item="1"]'));
        buttons.forEach((btn, idx) => {
            btn.setAttribute('aria-selected', idx === activeIndex ? 'true' : 'false');
        });
    }

    function renderLoading() {
        isLoading = true;
        setActiveIndex(-1);
        list.innerHTML = `
            <div class="ac-item" role="option" aria-selected="false">
                <div>
                    <div class="ac-title">Buscando...</div>
                    <div class="ac-sub">Aguarde um instante.</div>
                </div>
                <span class="ac-pill">...</span>
            </div>
        `;
        setPanelOpen(panel, input, true);
    }

    function renderEmpty(q) {
        isLoading = false;
        items = [];
        setActiveIndex(-1);
        list.innerHTML = `
            <div class="ac-item" role="option" aria-selected="false">
                <div>
                    <div class="ac-title">Nenhum produto encontrado</div>
                    <div class="ac-sub">Tente buscar por EAN, SKU ou parte do nome.</div>
                </div>
                <span class="ac-pill ac-pill--danger">0</span>
            </div>
            <button type="button" class="ac-item" data-ac-submit="1">
                <div>
                    <div class="ac-title">Pesquisar por "${esc(q)}"</div>
                    <div class="ac-sub">Abrir a listagem com este termo.</div>
                </div>
                <span class="ac-pill">Enter</span>
            </button>
        `;
        const btn = list.querySelector('button[data-ac-submit="1"]');
        if (btn) btn.addEventListener('click', () => submitForm());
        setPanelOpen(panel, input, true);
    }

    function chooseItem(p, q) {
        const nextValue = String(p?.ean || p?.sku || p?.name || q).trim();
        if (nextValue) input.value = nextValue;
        setPanelOpen(panel, input, false);
        submitForm();
    }

    function renderResults(q, products) {
        isLoading = false;
        items = Array.isArray(products) ? products : [];
        setActiveIndex(items.length ? 0 : -1);

        const rows = items.map((p, idx) => {
            const name = esc(p?.name || 'Produto');
            const ean = p?.ean ? esc(p.ean) : null;
            const sku = p?.sku ? esc(p.sku) : null;
            const price = p?.price !== null && p?.price !== undefined ? fmtMoney(p.price) : '-';
            const stockTotal = Number.isFinite(Number(p?.stock_total)) ? Number(p.stock_total) : null;

            const subParts = [];
            if (ean) subParts.push(`EAN: <span class="font-mono">${ean}</span>`);
            if (!ean && sku) subParts.push(`SKU: <span class="font-mono">${sku}</span>`);
            if (stockTotal !== null) subParts.push(`Estoque total: <span class="font-semibold">${esc(stockTotal)}</span>`);

            const pillClass = stockTotal !== null && stockTotal <= 0 ? 'ac-pill ac-pill--danger' : 'ac-pill';
            const pillText = stockTotal !== null ? `Estq. ${esc(stockTotal)}` : price;

            return `
                <button
                    type="button"
                    class="ac-item"
                    role="option"
                    aria-selected="${idx === 0 ? 'true' : 'false'}"
                    data-ac-item="1"
                    data-ac-index="${idx}"
                >
                    <div class="min-w-0">
                        <div class="ac-title truncate">${name}</div>
                        ${subParts.length ? `<div class="ac-sub">${subParts.join(' <span class="mx-2 text-slate-300">|</span> ')}</div>` : ''}
                    </div>
                    <span class="${pillClass}">${pillText}</span>
                </button>
            `;
        }).join('');

        list.innerHTML = `
            ${rows || ''}
            <button type="button" class="ac-item" data-ac-submit="1">
                <div class="min-w-0">
                    <div class="ac-title">Pesquisar por "${esc(q)}"</div>
                    <div class="ac-sub">Abrir a listagem com este termo.</div>
                </div>
                <span class="ac-pill">Enter</span>
            </button>
        `;

        list.querySelectorAll('button[data-ac-item="1"]').forEach((btn) => {
            btn.addEventListener('mousemove', () => {
                const idx = Number.parseInt(btn.dataset.acIndex || '-1', 10);
                if (Number.isFinite(idx) && idx >= 0) setActiveIndex(idx);
            });
            btn.addEventListener('click', () => {
                const idx = Number.parseInt(btn.dataset.acIndex || '-1', 10);
                if (!Number.isFinite(idx) || idx < 0) return;
                chooseItem(items[idx], q);
            });
        });

        const submitBtn = list.querySelector('button[data-ac-submit="1"]');
        if (submitBtn) submitBtn.addEventListener('click', () => submitForm());

        setPanelOpen(panel, input, true);
    }

    async function fetchResults(q) {
        if (abort) abort.abort();
        abort = new AbortController();

        const usp = new URLSearchParams();
        usp.set('q', q);
        usp.set('limit', String(Number.isFinite(limit) ? limit : 8));

        const url = apiUrl + (apiUrl.includes('?') ? '&' : '?') + usp.toString();

        renderLoading();

        try {
            const res = await fetch(url, {
                method: 'GET',
                headers: { Accept: 'application/json' },
                signal: abort.signal,
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            const products = Array.isArray(data?.products) ? data.products : [];
            if (!products.length) {
                renderEmpty(q);
                return;
            }
            renderResults(q, products);
        } catch (err) {
            if (err && typeof err === 'object' && err.name === 'AbortError') return;
            isLoading = false;
            items = [];
            setActiveIndex(-1);
            list.innerHTML = `
                <div class="ac-item" role="option" aria-selected="false">
                    <div>
                        <div class="ac-title">Falha ao buscar</div>
                        <div class="ac-sub">Verifique sua conexao e tente novamente.</div>
                    </div>
                    <span class="ac-pill ac-pill--danger">!</span>
                </div>
            `;
            setPanelOpen(panel, input, true);
        }
    }

    const onInput = debounce(() => {
        const q = String(input.value || '').trim();
        usedArrows = false;

        if (q.length < (Number.isFinite(minChars) ? minChars : 2)) {
            items = [];
            setActiveIndex(-1);
            setPanelOpen(panel, input, false);
            return;
        }

        fetchResults(q);
    }, 160);

    input.addEventListener('input', onInput);
    input.addEventListener('focus', () => {
        const q = String(input.value || '').trim();
        if (q.length >= (Number.isFinite(minChars) ? minChars : 2) && list.childElementCount) {
            setPanelOpen(panel, input, true);
        }
    });

    input.addEventListener('keydown', (e) => {
        const isOpen = panel.dataset.open === '1';

        if (e.key === 'Escape') {
            if (isOpen) {
                e.preventDefault();
                setPanelOpen(panel, input, false);
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            usedArrows = true;
            if (!isOpen) {
                const q = String(input.value || '').trim();
                if (q.length >= (Number.isFinite(minChars) ? minChars : 2) && !isLoading) setPanelOpen(panel, input, true);
            }
            if (!items.length) return;
            e.preventDefault();
            setActiveIndex(Math.min(items.length - 1, activeIndex + 1));
            return;
        }

        if (e.key === 'ArrowUp') {
            usedArrows = true;
            if (!items.length) return;
            e.preventDefault();
            setActiveIndex(Math.max(0, activeIndex - 1));
            return;
        }

        if (e.key === 'Enter') {
            if (!isOpen) return;
            if (!usedArrows) return;
            if (activeIndex < 0 || activeIndex >= items.length) return;
            e.preventDefault();
            chooseItem(items[activeIndex], String(input.value || '').trim());
        }
    });

    input.addEventListener('blur', () => {
        window.setTimeout(() => {
            if (document.activeElement && panel.contains(document.activeElement)) return;
            setPanelOpen(panel, input, false);
        }, 120);
    });

    panel.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            setPanelOpen(panel, input, false);
            input.focus();
        }
    });

    panel.addEventListener('mousedown', (e) => {
        // Prevent input blur before click handlers run.
        e.preventDefault();
    });

    document.addEventListener('click', (e) => {
        const target = e.target;
        if (!(target instanceof Node)) return;
        if (form.contains(target) || panel.contains(target)) return;
        setPanelOpen(panel, input, false);
    });
}
