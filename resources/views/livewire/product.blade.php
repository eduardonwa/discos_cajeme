<div class="product | container" data-type="wide">
    <header class="product__header">
        @if ($this->product->promo_label)
            <span class="badge" data-type="product-page">{{ $this->product->promo_label }}</span>
        @endif

        <h2 class="name | ff-semibold">{{ $this->product->name }}</h2>
        <div class="reviews-summary" aria-live="polite">
            <span class="reviews__stars" aria-hidden="true">★★★★★</span>
            <span class="sr-only">Calificación promedio 4.7 de 5 basada en 128 reseñas</span>
        </div>
        <hr line-type="inner" data-device="d">
    </header>

    <x-ui.product-gallery 
        :featured="[
            'thumb' => $this->product->getFirstMediaUrl('featured', 'sm_thumb'),
            'large' => $this->product->getFirstMediaUrl('featured', 'lg_thumb'),
        ]"
        :images="$this->allProductImages"
        :name="$this->product->name"
    />

    <section class="product__info">
        @if ($this->product->variants->isNotEmpty())
            <div class="variants">
                @foreach ($this->product->variants as $v)
                    @php
                        $isDisabled = $v->total_variant_stock == 0 || ! $v->is_active;
                        $labelTxt = $v->attributes
                            ->map(fn($av) => ($av->attribute->key ?? '').': '.$av->value)
                            ->implode('<br>');
                    @endphp

                    <div class="variant">
                        <input
                            type="radio"
                            id="variant-{{ $v->id }}"
                            name="variant"
                            value="{{ $v->id }}"
                            wire:model.live="variant"
                            @disabled($isDisabled)
                            class="variant__input"
                        >
                            <label for="variant-{{ $v->id }}" class="variant__label" title="{{ $isDisabled ? 'Producto agotado' : '' }}">
                                {!! $labelTxt !!} @if($v->total_variant_stock==0) (Agotado) @endif
                            </label>
                    </div>
                @endforeach

                @error('variant')
                    <div class="text-error">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <x-ui.price-tag 
            :finalPrice="$this->product->price" 
            :originalPrice="$this->originalPrice"
        />
        
        <div class="details"
                x-cloak
                x-data="{
                tab: 'details',
                isTabs: window.innerWidth < 1280
                }"
                @resize.window="isTabs = window.innerWidth < 1280"
                aria-label="Detalles del producto"
        >
            <hr line-type="inner" data-device="d">

            {{-- tab headers --}}
            <div class="tabs">
                <button
                    @click="tab = 'details'"
                    class="button"
                    data-type="tab"
                    :class="tab === 'details' ? 'tabs--active' : ''"
                    :aria-expanded="tab === 'details'"
                >Detalles del producto</button>

                <button
                    @click="tab = 'envio'"
                    class="button"
                    data-type="tab"
                    :class="tab === 'envio' ? 'tabs--active' : ''"
                    :aria-expanded="tab === 'envio'"
                >Envío y manipulación</button>

                <button
                    @click="tab = 'description'"
                    class="button"
                    data-type="tab"
                    :class="tab === 'description' ? 'tabs--active' : ''"
                    :aria-expanded="tab === 'description'"
                >Descripción</button>
            </div>
            {{-- tab content --}}
            <div class="content">
                <div class="content__section"
                        x-bind:hidden="isTabs && tab !== 'details'">
                    <h3 class="subheader | ff-semibold fs-600">Detalles del producto</h3>
                    <p class="padding-block-start-1">Material: 100% algodón. Hecho en México</p>
                </div>
    
                <div class="content__section"
                        x-bind:hidden="isTabs && tab !== 'envio'">
                    <h3 class="subheader | ff-semibold fs-600">Envío y manipulación</h3>
                    <p class="padding-block-start-1">Envío 2-5 días hábiles. Cambios y devoluciones en 30 días.</p>
                </div>
    
                <div class="content__section"
                        x-bind:hidden="isTabs && tab !== 'description'">
                    <h3 class="subheader | ff-semibold fs-600">Descripción</h3>
                    <p class="padding-block-start-1">{!! nl2br(e($this->product->description)) !!}</p>
                </div>
            </div>

        </div>

        <hr line-type="inner" data-device="m">
    </section>
    
    <aside class="product__action" aria-labelledby="purchase-heading">
        <h2 id="purchase-heading" class="sr-only">Comprar {{ $this->product->name }}</h2>
        
        <p class="price">{{ $this->product->price }}</p>

        {{-- disponibilidad --}}
        <p class="stock {{ $this->availableStock > 0 ? 'text-success' : 'text-error' }}">
            @if($this->availableStock>0)
                {{ $this->availableStock }} disponibles
                @if($this->availableStock <= $this->product->low_stock_threshold) (¡Últimas unidades!) @endif
            @else Agotado @endif
        </p>

        {{-- cantidad --}}
        @include('components.ui.product-quantity-picker', [
            'quantity' => $this->quantity,
            'max'      => $this->maxQuantity,
            'prop'     => 'quantity',
            'disabled' => $this->availableStock < 1,
            'label'    => 'Cantidad',
        ])

        <button
            class="button"
            data-type="cart"
            wire:click="addToCart"
            @disabled($this->availableStock < 1)"
        >
            {{ $this->availableStock > 0 ? 'Añadir al carrito' : 'AGOTADO' }}
        </button>

        {{-- cupones --}}
        @unless($this->product->total_product_stock < 0)
            <livewire:coupon-form context="product" :targetId="$productId"/>
        @endunless
    </aside>

    <hr line-type="base" data-device="m">

    <section class="product__related-products"></section>
    
    <section class="product__reviews">
        <h2 class="header">La confianza también se viste</h2>

        <div class="review">
            <p class="stars">★★★★★</p>
            <p class="author">Lenina Crowne</p>
            <p class="text">
                Me encanta cuando todo es simple. Si se ve bien y está de moda, lo quiero ahora.
            </p>
        </div>

        <div class="review">
            <p class="stars">★★★★☆</p>
            <p class="author">Thomas</p>
            <p class="text">
                El orden comienza en cómo vestimos. Cada prenda tiene su función.
            </p>
        </div>
    </section>
</div>