<div class="container" data-type="wide">

    <h2 class="heading-2">{{ $this->latestProdsHeader }}</h2>
    
    <section class="latest-products">
        @foreach ($products as $product)
            <div class="card card--latest-prods flow">
                <div class="card__meta">
                    <h2 class="heading">{{ $product->name }}</h2>
                </div>
                
                <div class="card__media">
                    <img
                        class="image"
                        src="{{ $product->getFirstMediaUrl('featured', 'md_thumb') }}"
                        alt="{{ $product->name }}"
                    >
                    <div class="overlay">
                        <a class="button" data-type="secondary" href="{{ route('product', $product->slug) }}">Ver producto</a>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</div>