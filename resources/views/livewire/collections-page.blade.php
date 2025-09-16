<div
    x-data="filtersShell()"
    x-init="init()"
    x-cloak
    x-effect="document.body.classList.toggle('menu-open', !isDesktop && $store.ui.filtersOpen)"
>
    <main class="p-collections container" data-type="wide">
        {{-- info --}}
        <header class="p-collections__header">
            <h2 class="name">{{ $collection->name }}</h2>
            <p class="description">{{ $collection->description }}</p>
        </header>

        {{-- filtros --}}
        <section
            class="p-collections__filters"
            x-cloak
            id="filters-panel"
            x-show="isDesktop || $store.ui.filtersOpen"
            x-trap.noscroll.inert="!isDesktop && $store.ui.filtersOpen"
            @keydown.escape.window="!isDesktop && ($store.ui.filtersOpen=false)"
            x-transition:enter="slide-enter"
            x-transition:enter-start="slide-enter-start"
            x-transition:enter-end="slide-enter-end"
            x-transition:leave="slide-leave"
            x-transition:leave-start="slide-leave-start"
            x-transition:leave-end="slide-leave-end"
            :class="isDesktop ? 'p-collections-filter mode-sidebar' : 'p-collections-filter mode-sheet'"
            role="dialog" aria-modal="true" aria-label="Filtros"
        >
            <header>
                <h2 class="ff-semibold">Filtros</h2>
                <x-icon 
                    x-show="!isDesktop" 
                    @click="$store.ui.filtersOpen = false" 
                    label="Cerrar filtros"
                >
                    <x-ui.icons.close /> 
                </x-icon>
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
        <section class="p-collections__products">
            @if ($hero)
                <x-ui.product-card
                    :product="$hero"
                    :href="route('product', $hero)"
                    variant="minimal"
                    class="is-hero"
                />
            @endif

            @foreach ($products as $product)

                <x-ui.product-card
                    :product="$product"
                    :href="route('product', $product)"
                    variant="minimal"
                    :badge="$product->badge"
                />
            @endforeach
        </section>
    </main>
    {{ $products->links() }}
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