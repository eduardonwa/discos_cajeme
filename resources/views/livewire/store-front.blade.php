<div>
    <div>
        <x-input wire:model.live.debounce="searchQuery" type="text" placeholder="Escribe algo"/>
        <div wire:loading.delay.shorter wire:target="searchQuery">Buscando...</div>
    </div>

    @if (filled($this->searchQuery))
        @if($this->productsQuery->count())
            <div>
                {{-- tarjeta de producto --}}
                @foreach ($this->productsQuery as $product)
                    <article>
                        <a
                            wire:navigate
                            href="{{ route('product', $product) }}"
                        >
                            <img
                                src="{{ $product->getFirstMediaUrl('images') }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                            >
                            <h2>{{ $product->name }}</h2>
                            <p>{{ $product->price }}</p>
                        </a>
                    </article> 
                @endforeach
            </div>

            {{-- paginacion --}}
            @if ($this->productsQuery->hasPages())
                <div>{{ $this->productsQuery->links() }}</div>
            @endif

            {{-- resultados --}}
            @else
                <p>{{ $this->searchQuery }}</p>
        @endif
    @endif
    
    <livewire:hero-slider :slides="$heroSlider" wire:key="hero-slider" />

    <div class="container" data-type="wide">
        <x-collections-carousel :collection="$verano" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$onSale" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$atemporal" type="full-price" />
        <x-collections-carousel :collection="$comodidad" type="full-price" />
    </div>
</div>
