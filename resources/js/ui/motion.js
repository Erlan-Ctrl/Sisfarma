// Small motion system for admin pages (no framework).
// Adds staggered "reveal" animations + subtle lift for clickable cards.

function prefersReducedMotion() {
    try {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    } catch {
        return false;
    }
}

/**
 * Apply motion enhancements to current page.
 */
export function initMotion() {
    const root = document.querySelector('[data-page-root="1"]');
    if (!(root instanceof HTMLElement)) return;

    const candidates = Array.from(root.querySelectorAll([
        'a.rounded-3xl.shadow-sm',
        'div.rounded-3xl.shadow-sm',
        'section.rounded-3xl.shadow-sm',
        'a.rounded-2xl.shadow-sm',
        'div.rounded-2xl.shadow-sm',
        'section.rounded-2xl.shadow-sm',
    ].join(',')));

    if (!candidates.length) return;

    let idx = 0;
    for (const el of candidates) {
        if (!(el instanceof HTMLElement)) continue;

        el.classList.add('reveal');
        el.style.setProperty('--reveal-delay', `${Math.min(idx, 14) * 55}ms`);
        idx += 1;

        if (el.tagName === 'A') {
            el.classList.add('interactive-card');
        }
    }

    if (prefersReducedMotion()) {
        candidates.forEach((el) => el.classList.add('reveal--in'));
        return;
    }

    if (!('IntersectionObserver' in window)) {
        candidates.forEach((el) => el.classList.add('reveal--in'));
        return;
    }

    const io = new IntersectionObserver((entries, observer) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            const target = entry.target;
            if (target instanceof HTMLElement) {
                target.classList.add('reveal--in');
            }
            observer.unobserve(target);
        }
    }, {
        threshold: 0.15,
        rootMargin: '0px 0px -10% 0px',
    });

    candidates.forEach((el) => io.observe(el));
}

