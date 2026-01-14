import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';

import focus from '@alpinejs/focus';

document.addEventListener('alpine:init', () => {
    // focus
    Alpine.plugin(focus)

    // store global
    Alpine.store('ui', { navOpen:false, filtersOpen:false });
                    
    // componente reutilizable para breakpoints
    Alpine.data('filtersShell', () => ({
        isDesktop: false,
        init() {
            const mql = window.matchMedia('(min-width: 1024px)');
            const set = e => {
                this.isDesktop = e.matches;
                if (this.isDesktop) $store.ui.filtersOpen = false; // cierra sheet al pasar a desktop
            };
            set(mql);
            (mql.addEventListener ? mql.addEventListener('change', set) : mql.addListener(set));
        }
    }));
});