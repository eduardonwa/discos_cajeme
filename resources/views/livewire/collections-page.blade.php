<div x-data="filtersShell()" x-init="init()"
     x-cloak
     x-effect="document.body.classList.toggle('menu-open', !isDesktop && $store.ui.filtersOpen)"
>
    <div class="section">
        <div class="container" data-type="wide">
            {{-- info --}}
            <h2>{{ $collection->name }}</h2>
            <p>{{ $collection->description }}</p>
            
            <section id="filters-panel" x-cloak
                x-show="isDesktop || $store.ui.filtersOpen"
                x-trap.noscroll.inert="!isDesktop && $store.ui.filtersOpen"
                @keydown.escape.window="!isDesktop && ($store.ui.filtersOpen=false)"
                x-transition:enter="slide-enter"
                x-transition:enter-start="slide-enter-start"
                x-transition:enter-end="slide-enter-end"
                x-transition:leave="slide-leave"
                x-transition:leave-start="slide-leave-start"
                x-transition:leave-end="slide-leave-end"
                :class="isDesktop ? 'collections-filter mode-sidebar' : 'collections-filter mode-sheet'"
                role="dialog" aria-modal="true" aria-label="Filtros"
            >
                <header>
                    <h2 class="ff-semibold">Filtros</h2>
                    <button x-show="!isDesktop" @click="$store.ui.filtersOpen = false" aria-label="Cerrar filtros">✕</button>
                </header>

                <livewire:filters-panel
                    wire:model="filters"
                    :collection="$collection"
                    :key="'filters-'.$collection->id"
                />

                <footer x-show="!isDesktop">
                    <button
                        @click="$store.ui.filtersOpen=false"
                        type="button"
                        class="button uppercase"
                        data-type="ghost"
                    >
                        Ver resultados
                    </button>
                </footer>
            </section>

            {{-- productos --}}
            @foreach ($products as $product)
                <article>
                    <h2>{{ $product->name }}</h2>
                    <p>{{ $product->price }}</p>
                </article>
            @endforeach
        </div>
    </div>
</div>

<script>
function filtersShell(){
  return {
    isDesktop: false,
    init(){
      const mql = window.matchMedia('(min-width: 1280px)');
      const apply = e => {
        this.isDesktop = e.matches;
        if (this.isDesktop) $store.ui.filtersOpen = false; // cierra sheet al pasar a desktop
      };
      apply(mql);
      (mql.addEventListener ? mql.addEventListener('change', apply) : mql.addListener(apply));
    }
  }
}
</script>