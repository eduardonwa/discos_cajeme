<div class="container" data-type="wide">

    <h2 class="heading-2">{{ $this->latestProdsHeader }}</h2>
    
    <section class="latest-products">
        @foreach ($latestItems as [
            'product' => $product,
            'variant' => $variant,
            'price' => $price,
            'compare_at_price' => $compareAt
        ])
            <div class="card card--latest-prods flow">
                <div class="card__meta">
                    <h2 class="heading">{{ $product->name }}</h2>
                    @if($variant)
                        <span class="badge" data-type="variant-attribute">{{ $variant->title }}</span>
                    @endif
                    
                    <div class="price">    
                        @if($compareAt && $compareAt > $price)
                            <p class="line-through">
                                @money($compareAt)
                            </p>
                        @endif
                        <p class="clr-neutral-900">@money($price)</p>
                    </div>
                </div>
                
                <div class="card__media">
                    <img
                        class="image"
                        src="{{ $product->getFirstMediaUrl('featured', 'md_thumb') }}"
                        alt="{{ $product->name }}"
                    >
                    <div class="overlay">
                        <x-icon 
                            href="{{ route('product', $product->slug) }}"
                            class="button"
                            data-type="secondary"
                            label="Ver producto"
                        >
                            <x-ui.icons.view />
                        </x-icon>
                        
                        <form method="POST" action="{{ route('buy-now') }}">
                            @csrf
                            <input type="hidden" name="variant_id" value="{{ $variant?->id }}">
                            <input type="hidden" name="qty" value="1">

                            <x-icon
                                type="submit"
                                class="button"
                                data-type="buy-now"
                                :disabled="(!$variant)"
                                label="Comprar ahora"
                            >
                                <x-ui.icons.thin-arrow />
                            </x-icon>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </section>
</div>