import './bootstrap';

import { initLineItemsForms } from './admin/lineItemsForm';
import { initFlash } from './ui/flash';
import { initHeaderProductSearchAutocomplete } from './ui/productSearchAutocomplete';
import { initMotion } from './ui/motion';
import { initUserMenuDropdown } from './ui/userMenuDropdown';
import { initSidebarCollapse } from './ui/sidebarCollapse';

function start() {
    initLineItemsForms();
    initFlash();
    initHeaderProductSearchAutocomplete();
    initUserMenuDropdown();
    initSidebarCollapse();
    initMotion();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
