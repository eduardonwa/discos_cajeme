@if (filled($this->searchQuery))
    @if($this->productsQuery->count())
        <div class="results-grid">
            @foreach ($this->productsQuery as $product)
                <article>
                    <a wire:navigate href="{{ route('product', $product) }}">
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

        @if ($this->productsQuery->hasPages())
            <div class="pagination">{{ $this->productsQuery->links() }}</div>
        @endif
    @else
        <p>No se encontraron resultados para "{{ $this->searchQuery }}"</p>
    @endif
@endif
