<div class="container" data-type="wide">
    <section class="cart">
        @if ($this->cart->items->isEmpty())
            @if($showError && $emptyCart)
                <div class="cart__empty">
                    <h2 class="ff-bold fs-700">{{ $emptyCart }}</h2>
                    <a href="/" class="button" data-type="catalogue">Ve nuestro catálogo</a>
                </div>
            @endif
        @else
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
                        <figure class="item__media">
                            <img
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

                {{-- <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Imagen</th>
                            <th class="text-left">Producto</th>
                            <th class="text-left">Precio</th>
                            <th class="text-left">Información</th>
                            <th class="text-left">Cantidad</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->items as $item)
                            <tr>
                                <td>
                                    <img src="{{ $item->product->getFirstMediaUrl('featured', 'sm_thumb') }}">
                                </td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->product->price }}</td>
                                
                                @if($item->variant)
                                    <td>
                                        @foreach ($item->variant->attributes as $attributeVariant)
                                            {{ $attributeVariant->attribute->key . ':' ?? '' }} {{ $attributeVariant->value }}
                                        @endforeach
                                    </td>
                                @endif
                                
                                <td class="flex items-center">
                                    <button wire:click="decrement({{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    </button>
                                    
                                    <div>{{ $item->quantity }}</div>
        
                                    <button wire:click="increment({{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                </td>
        
                                <td class="text-right">
                                    {{ $item->subtotal }}
                                </td>
                                
                                <td class="pl-2">
                                    <button wire:click="delete({{ $item->id }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>                              
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-right font-medium">Subtotal</td>
                            <td class="font-medium text-right">
                                ${{ number_format($this->cart->items->sum(fn($item) => $item->product->price->getAmount() * $item->quantity) / 100, 2) }}
                            </td>
                            <td></td>
                        </tr>
        
                        @if ($this->discountDetails)
                            <tr class="text-green-600">
                                <td colspan="5" class="text-right font-medium">
                                    Descuento ({{ $this->discountDetails['code'] }})
                                    <button wire:click="removeCoupon" class="ml-2 text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="font-medium text-right">
                                    -${{ number_format($this->totalWithDiscount() / 100, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        @endif
        
                        <tr>
                            <td colspan="5" class="text-right font-bold">Total</td>
                            <td class="font-bold text-right">
                                ${{ number_format($this->totalWithDiscount / 100, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table> --}}
        
                <livewire:coupon-form 
                    context="cart" 
                    :targetId="$this->cart->id"
                />
            </x-order-panel>
        
            <x-order-panel title="Proceder al pago">
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

                    <button class="button" data-type="mobile-full" wire:click="checkout">Confirmar pedido</button>
                </div>
            </x-order-panel>
        @endif
    </section>
</div>
