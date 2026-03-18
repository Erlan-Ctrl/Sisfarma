function prefersReducedMotion() {
    try {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch {
        return false;
    }
}

function closeFlash(el) {
    if (!(el instanceof HTMLElement)) return;
    if (el.dataset.flashClosing === '1') return;
    el.dataset.flashClosing = '1';

    if (prefersReducedMotion()) {
        el.remove();
        return;
    }

    el.classList.remove('flash-animate-in');
    el.classList.add('flash-animate-out');

    const remove = () => el.remove();
    el.addEventListener('animationend', remove, { once: true });
    window.setTimeout(remove, 320);
}

export function initFlash() {
    const flashes = Array.from(document.querySelectorAll('[data-flash="1"]'));
    if (!flashes.length) return;

    for (const el of flashes) {
        if (!(el instanceof HTMLElement)) continue;

        const closeBtn = el.querySelector('[data-flash-close="1"]');
        if (closeBtn instanceof HTMLElement) {
            closeBtn.addEventListener('click', () => closeFlash(el));
        }

        const autodismiss = Number.parseInt(el.dataset.flashAutodismiss ?? '0', 10);
        if (Number.isFinite(autodismiss) && autodismiss > 0) {
            window.setTimeout(() => closeFlash(el), autodismiss);
        }
    }
}

