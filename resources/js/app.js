import './bootstrap';

import { initLineItemsForms } from './admin/lineItemsForm';
import { initFlash } from './ui/flash';
import { initHeaderProductSearchAutocomplete } from './ui/productSearchAutocomplete';
import { initMotion } from './ui/motion';

function start() {
    initLineItemsForms();
    initFlash();
    initHeaderProductSearchAutocomplete();
    initMotion();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
} else {
    start();
}
