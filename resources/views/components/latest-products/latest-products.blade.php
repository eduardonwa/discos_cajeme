<div class="container" data-type="wide">
    <section class="latest-products">
        @foreach ($variants as $variant)
            <div class="card card--latest-prods flow">
                <div class="card__meta">
                    <h2 class="heading">{{ $variant->product->name }}</h2>
                </div>
                
                <div class="card__media">
                    <img
                        class="image"
                        src="{{ $variant->product->getFirstMediaUrl('featured', 'md_thumb') }}"
                        alt="{{ $variant->product->title }}"
                    >
                </div>
            </div>
        @endforeach
    </section>
</div>