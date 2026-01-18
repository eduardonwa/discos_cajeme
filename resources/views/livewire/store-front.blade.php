<div class="above-fold
    {{ filled($this->searchQuery) ? 'is-searching' : '' }}
    {{ $searchModal ? 'is-search-modal-open' : '' }}"
>
    
    <livewire:hero-slider :slides="$heroSlider" wire:key="hero-slider" />

    <div class="container" data-type="wide">
        <x-collections-carousel :collection="$verano" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$onSale" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$atemporal" type="full-price" />
        <x-collections-carousel :collection="$comodidad" type="full-price" />
    </div>

    <div class="hero-search">
        {{-- DESKTOP CONTEXT --}}
        @if (!$searchModal)
            <section class="hero-search__desktop" aria-label="Búsqueda (Desktop)">
                {{-- Input visible en desktop --}}
                <header class="desktop-input">
                    <h2 class="heading-2">Encuentra lo que necesitas</h2>
                    @include('components.hero.hero-search-input')
                </header>

                {{-- Resultados overlay en desktop --}}
                <div class="desktop-results" aria-live="polite">
                    @include('components.hero.hero-search-results')
                </div>
            </section>
        @endif

        {{-- MOBILE CONTEXT --}}
        <section class="hero-search__mobile" aria-label="Búsqueda (Mobile)">
            {{-- Trigger (solo mobile) --}}
            <header class="hero-search__mobile-header">
                <h2 class="heading-2">Encuentra lo que necesitas</h2>
                <button
                    class="hero-search__mobile-trigger"
                    type="button"
                    wire:click="$set('searchModal', true)"
                    aria-haspopup="dialog"
                    aria-controls="heroSearchModal"
                >
                    Buscar
                </button>
            </header>
            
            {{-- Modal (solo mobile) --}}
            @if($searchModal)
                <div
                    class="hero-search__mobile-modal"
                    id="heroSearchModal"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Buscar productos"
                >
                    <div class="hero-search__mobile-backdrop" wire:click="$set('searchModal', false)" aria-hidden="true"></div>
                        <div class="hero-search__mobile-panel" role="document">
                            <button
                                class="hero-search__mobile-close"
                                type="button"
                                aria-label="Cerrar"
                                wire:click="$set('searchModal', false)"
                            >
                                ✕
                            </button>
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
</div>
