<div class="hero-search">
    <section class="hero-search__mobile"
        aria-label="Búsqueda (Mobile)"
        x-data="{ open: @entangle('searchModal').live }"
        x-on:open-search.window="$wire.open()"
    >
        @if($searchModal)
            <div
                class="hero-search__mobile-modal"
                id="heroSearchModal"
                role="dialog"
                aria-modal="true"
                aria-label="Buscar productos"
                x-trap.inert.noscroll="open"
            >
                <div class="backdrop" @click="$wire.close()" aria-hidden="true"></div>
                <div class="mobile-panel" role="document">
                    <div class="mobile-panel__top">
                        <h2>
                            Búsqueda
                        </h2>
                        <x-icon
                            :size="24"
                            aria-label="Cerrar"
                            @click="$wire.close()"
                        >
                            <x-ui.icons.close />
                        </x-icon>
                    </div>
                    
                    <div class="hero-search__mobile-input">
                        @include('components.hero.hero-search-input')
                    </div>

                    <div class="hero-search__mobile-results" aria-live="polite">
                        @include('components.hero.hero-search-results')
                    </div>
                </div>
            </div>
        @endif
    </section>
</div>