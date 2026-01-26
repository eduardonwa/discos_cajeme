<div>
    <x-input wire:model.live.debounce="searchQuery" type="text" placeholder="Escribe algo"/>
    <div wire:loading.delay.shorter wire:target="searchQuery">Buscando...</div>

    @if (filled($this->searchQuery))
        @if($products->count())
            <div class="results-grid">
                @foreach ($products as $product)
                    <article>
                        <a class="product-search" wire:navigate href="{{ route('product', $product) }}">
                            <img
                                src="{{ $product->getFirstMediaUrl('featured', 'lg_thumb') }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                            />
                            <div class="product-search__info">
                                <h2>{{ $product->name }}</h2>
                                <p>{{ $product->price }}</p>
                            </div>
                        </a>
                    </article> 
                @endforeach
            </div>
            <p>No se encontraron resultados para "{{ $this->searchQuery }}"</p>
        @endif
    @endif
</div>
