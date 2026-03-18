const STORAGE_KEY = 'sisfarma.sidebar.collapsed';

function readStoredState() {
    try {
        return window.localStorage.getItem(STORAGE_KEY);
    } catch {
        return null;
    }
}

function writeStoredState(value) {
    try {
        window.localStorage.setItem(STORAGE_KEY, value ? '1' : '0');
    } catch {
        // ignore
    }
}

export function initSidebarCollapse() {
    const sidebar = document.querySelector('[data-sidebar="1"]');
    if (!(sidebar instanceof HTMLElement)) return;

    const launcher = document.querySelector('[data-sidebar-launcher="1"]');

    const toggles = Array.from(document.querySelectorAll('[data-sidebar-toggle="1"]'))
        .filter((el) => el instanceof HTMLElement);

    if (!toggles.length) return;

    function setCollapsed(collapsed) {
        sidebar.dataset.collapsed = collapsed ? '1' : '0';
        sidebar.classList.toggle('sidebar--collapsed', collapsed);
        sidebar.classList.toggle('w-0', collapsed);
        sidebar.classList.toggle('w-72', !collapsed);
        sidebar.classList.toggle('pointer-events-none', collapsed);

        sidebar.setAttribute('aria-hidden', collapsed ? 'true' : 'false');

        if (launcher instanceof HTMLElement) {
            launcher.hidden = !collapsed;
        }

        toggles.forEach((btn) => {
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            btn.setAttribute('aria-label', collapsed ? 'Expandir menu' : 'Recolher menu');
        });

        writeStoredState(collapsed);
    }

    const stored = readStoredState();
    const startCollapsed = stored === '1';
    setCollapsed(startCollapsed);

    toggles.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            setCollapsed(sidebar.dataset.collapsed !== '1');
        });
    });
}
