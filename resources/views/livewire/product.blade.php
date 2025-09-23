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
    </header>

    <section class="product__media">
        <img class="mx-auto" src="{{ $this->product->getFirstMediaUrl('featured', 'lg_thumb') }}" alt="Featured Image">
        
        <div class="image-slider">
            <div class="navigation">
                <x-icon orientation="left">
                    <x-ui.icons.arrow />
                </x-icon>
    
                <x-icon>
                    <x-ui.icons.arrow />
                </x-icon>
            </div>

            <div class="track">
                @foreach ($this->allProductImages as $image)
                    <div class="track__item">
                        <img 
                            src="{{ $image['thumbnail'] }}" 
                            class=""
                            onerror="this.src='{{ $image['original'] }}'"
                        >
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <hr line-type="base">

    <section class="product__info">
        <div class="variants">
            @if ($this->product->variants->isNotEmpty())
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
            @endif

            
            @error('variant')
                <div class="text-error">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <hr line-type="inner">
        
        <div class="main-info">
            <div class="price">
                @if ($discountApplied || $this->hasDiscount)
                    @if ($this->originalPrice->greaterThan($this->finalPrice))
                        <span class="price__original">{{ $this->originalPrice }}</span>
                        <span class="price__final">{{ $this->finalPrice }}</span>
                    @else
                        <span class="price__final">{{ $this->finalPrice }}</span>
                    @endif
                @else
                    <span class="price__final">{{ $this->finalPrice }}</span>
                @endif
            </div>
    
            {{-- cantidad --}}
            <div
                class="quantity"
                x-data="{
                    open:false,
                    choose(n){ $wire.set('quantity', n); this.open=false; }
                }"
            >
                <small class="stock {{ $this->availableStock > 0 ? 'text-success' : 'text-error' }}">
                    @if($this->availableStock > 0)
                        {{ $this->availableStock }} disponibles
                        @if($this->availableStock <= $this->product->low_stock_threshold)
                            (¡Últimas unidades!)
                        @endif
                    @else
                        Agotado
                    @endif
                </small>

                <!-- Botón que parece select -->
                <button type="button" @click="open=true" class="quantity-trigger">
                    <span>Cantidad: {{ $this->quantity }}</span>
                    <svg width="16" height="16" viewBox="0 0 20 20"><path d="M5 7l5 6 5-6"/></svg>
                </button>

                <!-- Modal -->
                <div
                    class="quantity-modal"
                    aria-modal="true"
                    role="dialog"
                    x-show="open"
                    x-cloak
                    x-transition
                    @keydown.escape.window="open=false"
                >
                    <div class="quantity-modal__backdrop" @click="open=false"></div>
                    <div class="quantity-modal__content" x-trap.noscroll="open" @click.stop>
                        <div class="controls">
                            <h2 class="ff-semibold fs-500">Cantidad:</h2>
                            <x-icon @click="open=false" aria-label="Cerrar">
                                <x-ui.icons.close />
                            </x-icon>
                        </div>
                        <ul class="list-options">
                            @for ($i = 1; $i <= $this->maxQuantity; $i++)
                            <li>
                                <button
                                    type="button" @click="choose({{ $i }})"
                                    class="list-options__button {{ $this->quantity === $i ? 'list-options__button--checked' : '' }}"
                                >
                                    {{ $i }}
                                </button>
                            </li>
                            @endfor
                        </ul>
                    </div>
                </div>
            </div>
    
            {{-- agregar al carrito --}}
            <div class="action">
                <button
                    class="button"
                    data-type="cart"
                    wire:click="addToCart"
                    @disabled($this->availableStock < 1)
                >
                    {{ $this->availableStock > 0 ? 'Añadir al carrito' : 'AGOTADO' }}
                </button>
            </div>
        </div>

        <hr line-type="inner">

        {{-- cupones --}}
        {{-- @unless($this->product->total_product_stock < 0)
            <livewire:coupon-form context="product" :targetId="$productId"/>
        @endunless --}}

        {{-- detalles del producto --}}
        <div
            class="details"
            x-cloak
            x-data="{ tab: 'details' }"
            aria-label="Detalles del producto"
        >
            {{-- tab headers --}}
            <div class="tabs">
                <button
                    @click="tab = 'details'"
                    class="button"
                    data-type="tab"
                    :class="tab === 'details' ? 'tabs--active' : ''"
                >
                    Detalles
                </button>

                <button
                    @click="tab = 'envio'"
                    class="button"
                    data-type="tab"
                    :class="tab === 'envio' ? 'tabs--active' : ''"
                >
                    Envío y manipulación
                </button>

                <button
                    @click="tab = 'description'"
                    class="button"
                    data-type="tab"
                    :class="tab === 'description' ? 'tabs--active' : ''"
                >
                    Descripción
                </button>
            </div>
            {{-- tab content --}}
            <div class="content">
                <div class="content__section" x-show="tab === 'details'">
                    <p>Material: 100% algodón. Hecho en México</p>
                </div>
    
                <div class="content__section" x-show="tab === 'envio'">
                    <p>Envío 2-5 días hábiles. Cambios y devoluciones en 30 días.</p>
                </div>
    
                <div class="content__section" x-show="tab === 'description'">
                    <p>{!! nl2br(e($this->product->description)) !!}</p>
                </div>
            </div>
        </div>
    </section>

    <hr line-type="base">

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