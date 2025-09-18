<div class="container" data-type="wide">
    <header class="product__header">
        <span>{{ $this->product->promo_label }}</span>
        <h1 class="ff-semibold">{{ $this->product->name }}</h1>
    </header>

    <article class="product__media">
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
    </article>

    <article class="product__info">
        {{-- precio --}}
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
        
        {{-- cantidad y stock --}}
        <div class="quantity">
            <label for="qty">Cantidad</label>
            <input
                id="qty"
                type="number"
                min="1"
                max="{{ $this->maxQuantity }}"
                wire:model.lazy="quantity"
                @if($this->availableStock < 1) disabled @endif
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
        </div>

        {{-- agregar al carrito --}}
        <div class="action">
            @if ($this->product->variants->isNotEmpty())
                <select
                    wire:model.live="variant"
                    class="block w-full rounded-md border-0 py-1.5 pr-10 text-gray-800"
                >
                    @foreach ($this->product->variants as $variant)
                        <option value="{{ $variant->id }}"
                            @if($variant->total_variant_stock == 0) 
                                disabled
                                title="Producto agotado"
                                class="text-red-500 line-through"
                            @endif
                            @unless($variant->is_active) style="display: none;" @endunless
                        >
                            @foreach ($variant->attributes as $attributeVariant)
                                {{ $attributeVariant->attribute->key . ':' ?? '' }} {{ $attributeVariant->value }}
                                @if (!$loop->last) / @endif
                            @endforeach
                            @if($variant->total_variant_stock == 0) (Agotado) @endif
                        </option>
                    @endforeach
                </select>
            @endif
            
            @error('variant')
                <div class="mt-2 text-red-600">
                    {{ $message }}
                </div>
            @enderror

            <button
                class="button"
                data-type="cart"
                wire:click="addToCart"
                @disabled($this->availableStock < 1)
            >
                {{ $this->availableStock > 0 ? 'Añadir al carrito' : 'AGOTADO' }}
            </button>
        </div>

        {{-- cupones --}}
        @unless($this->product->total_product_stock < 0)
            <livewire:coupon-form context="product" :targetId="$productId"/>
        @endunless

        {{-- detalles, envio y descripcion --}}
        <div class="details" aria-label="Detalles del producto">
            <details open>
                <summary>Detalles</summary>
                <div>
                    <ul class="no-list-style">
                        <li>Material: 100& algodón</li>
                        <li>Hecho en México</li>
                    </ul>
                </div>
            </details>

            <details>
                <summary>Envío y manipulación</summary>
                <div>
                    <p>Envío 2-5 días hábiles. Cambios y devoluciones en 30 días.</p>
                </div>
            </details>

            <details>
                <summary>Descripción</summary>
                <div>
                    <p>{!! nl2br(e($this->product->description)) !!}</p>
                </div>
            </details>
        </div>
    </article>

    <article class="product__related-products"></article>
    
    <article class="product__testimonials">
        <h2>La confianza también se viste</h2>

        <div>
            <div>
                <div>estrellas</div>
                    <p>Lenina Crowne</p>
                    <p>Me encanta cuando todo es simple. Si se ve bien y está de moda, lo quiero ahora.</p>
                </div>
            </div>
            
            <div>
                <div>estrellas</div>
                <div>
                    <p>Thomas</p>
                    <p>El orden comienza en cómo vestimos. Cada prenda tiene su función.</p>
                </div>
            </div>
        </div>
    </article>
</div>