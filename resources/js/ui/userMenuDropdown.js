export function initUserMenuDropdown() {
    const menus = document.querySelectorAll('[data-user-menu="1"]');
    if (!menus.length) return;

    menus.forEach((root) => {
        const btn = root.querySelector('[data-user-menu-toggle="1"]');
        const panel = root.querySelector('[data-user-menu-panel="1"]');
        const chevron = root.querySelector('[data-user-menu-chevron="1"]');

        if (!btn || !panel) return;

        let open = false;
        let closeTimer = null;

        function setExpanded(value) {
            btn.setAttribute('aria-expanded', value ? 'true' : 'false');
            if (chevron) chevron.classList.toggle('rotate-180', value);
        }

        function showPanel() {
            if (!panel.hidden) return;
            panel.hidden = false;

            // Ensure transitions actually run when toggling hidden.
            panel.classList.remove('opacity-100', 'scale-100');
            panel.classList.add('opacity-0', 'scale-95');

            requestAnimationFrame(() => {
                panel.classList.remove('opacity-0', 'scale-95');
                panel.classList.add('opacity-100', 'scale-100');
            });
        }

        function hidePanel() {
            if (panel.hidden) return;
            panel.classList.remove('opacity-100', 'scale-100');
            panel.classList.add('opacity-0', 'scale-95');

            window.clearTimeout(closeTimer);
            closeTimer = window.setTimeout(() => {
                if (!open) panel.hidden = true;
            }, 160);
        }

        function openMenu({ focusFirst = false } = {}) {
            if (open) return;
            open = true;
            setExpanded(true);
            showPanel();

            if (focusFirst) {
                const first = panel.querySelector('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
                if (first) first.focus({ preventScroll: true });
            }
        }

        function closeMenu({ focusButton = false } = {}) {
            if (!open) return;
            open = false;
            setExpanded(false);
            hidePanel();

            if (focusButton) btn.focus({ preventScroll: true });
        }

        function toggleMenu() {
            if (open) closeMenu();
            else openMenu();
        }

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            toggleMenu();
        });

        btn.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                openMenu({ focusFirst: true });
            }
        });

        document.addEventListener('click', (e) => {
            if (!open) return;
            if (!root.contains(e.target)) closeMenu();
        });

        document.addEventListener('keydown', (e) => {
            if (!open) return;
            if (e.key === 'Escape') closeMenu({ focusButton: true });
        });

        panel.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeMenu({ focusButton: true });
        });
    });
}

