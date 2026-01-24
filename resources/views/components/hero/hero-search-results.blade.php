@php($products = $this->productsQuery)
@php($hasMore = $products->count() > 12)
@php($visible = $products->take(12))

@if (filled($this->searchQuery))
    @if($visible->count())
        <div class="results-grid">
            @foreach ($visible as $product)
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

        @if ($hasMore)
            <div class="results-more">
                <a class="button"
                   wire:navigate
                   href="{{ route('search', ['q' => $this->searchQuery]) }}"
                   @click="$wire.close()"
                >
                    Ver todos los resultados
                </a>
            </div>
        @endif
    @else
        <p>No se encontraron resultados para "{{ $this->searchQuery }}"</p>
    @endif
@endif
