<div class="container" data-type="wide">
    <section class="cart">
        @if ($this->cart->items->isEmpty())
            @if($showError && $emptyCart)
                <div class="cart__empty">
                    <h2 class="ff-bold fs-700">{{ $emptyCart }}</h2>
                    <a href="/" class="button" data-type="outline">Ve nuestro catálogo</a>
                </div>
            @endif
        @else
            <div class="cart__shell">
                <div class="cart-layout__left">
                    <x-order-panel title="Tu carrito">
                        {{-- header --}}
                        <header class="header">
                            <h3 class="header__item">Imagen</h3>
                            <h3 class="header__item">Producto</h3>
                            <h3 class="header__item">Precio</h3>
                            <h3 class="header__item">Información</h3>
                            <h3 class="header__item">Cantidad</h3>
                            <h3 class="header__item">Total</h3>
                        </header>
        
                        {{-- body --}}
                        @foreach ($this->items as $item)
                            @php $product = $item->resolvedProduct; @endphp
                            <article class="item">
                                {{-- Media --}}
                                <figure class="item__media u-media">
                                    <img
                                        class="u-media__img"
                                        src="{{ $item->product?->getFirstMediaUrl('featured', 'sm_thumb') }}"
                                        alt="{{ $item->product?->name }}"
                                        loading="lazy"
                                    >
                                </figure>
        
                                {{-- Info --}}
                                <div class="item__info">
                                    <h2 class="name">{{ $item->product?->name }}</h2>
                                    <p class="price">{{ $item->product?->price }}</p>
        
                                    @if($item->variant)
                                        <div class="variant">
                                            @foreach ($item->variant->attributes as $av)
                                                <span class="chip">
                                                    {{ $av->attribute->key ?? '' }}: {{ $av->value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
        
                                    <div class="subtotal">
                                        <p class="ff-semibold">Importe:</p>
                                        <span class="subtotal__amount">
                                            {{ $item->subtotal }}
                                        </span>
                                    </div>
                                </div>
        
                                {{-- Actions --}}
                                <footer class="item__actions">
                                    <div class="quantity">
                                        <x-icon wire:click="decrement({{ $item->id }})">
                                            <x-ui.icons.minus />
                                        </x-icon>
                                        
                                        <div>{{ $item->quantity }}</div>
        
                                        <x-icon wire:click="increment({{ $item->id }})">
                                            <x-ui.icons.plus />
                                        </x-icon>
                                    </div>
        
                                    <button class="button" data-type="cart-delete" wire:click="delete({{ $item->id }})">
                                        Eliminar
                                    </button>
                                </footer>
                            </article>
                        @endforeach
        
                        {{-- footer --}}
                        <footer class="footer">
                            {{-- subtotal --}}
                            <div class="subtotal">
                                <h2>Subtotal</h2>
                                <p>${{ number_format($this->cart->items->sum(fn($item) => $item->subtotal->getAmount()) / 100, 2) }}</p>
                            </div>
        
                            {{-- descuento --}}
                            <div class="discount text-success">
                                @if ($this->discountDetails)
                                    <div class="discount__content">
                                        <p>Cupon: <span class="ff-semibold text-success">"{{ $this->discountDetails['code'] }}"</span> </p>
                                        <p class="discounted-total">
                                            -${{ number_format($this->totalWithDiscount() / 100, 2) }}
                                        </p>
                                    </div>
                                @endif
                            </div>
        
                            {{-- total --}}
                            <div class="total">
                                <h2 class="">Total</h2>
                                <h2 class="ff-bold fs-600">
                                    ${{ number_format($this->totalWithDiscount / 100, 2) }}
                                </h2>
                            </div>
                        </footer>
                    </x-order-panel>
                </div>
    
                <aside class="cart-layout__right">
                    <livewire:coupon-form 
                        context="cart" 
                        :targetId="$this->cart->id"
                    />
                
                    <x-order-panel>
                        <div class="checkout">
                            @if($this->discountDetails)
                                <div class="coupon">
                                    <article class="code">
                                        <p>Cupón aplicado: <span>{{ $this->discountDetails['code'] }}</span></p>
                                        <p class="remove uppercase" wire:click="removeCoupon">Quitar</p>
                                    </article>
                                    <p class="discount ff-semibold">Descuento: {{ $this->discountDetails['formatted'] }}</p>
                                </div>
                            @endif
        
                            <button class="button" data-type="checkout" wire:click="checkout">Confirmar pedido</button>
                        </div>
                    </x-order-panel>
                </aside>
            </div>
        @endif
    </section>
</div>
