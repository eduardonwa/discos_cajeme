<div class="grid grid-cols-2 gap-10 py-12">
    <div class="space-y-4">
        <div class="bg-white p-5 rounded-lg shadow">
            <img class="mx-auto" src="{{ $this->product->getFirstMediaUrl('featured', 'lg_thumb') }}" alt="Featured Image">
        </div>
        
        <!-- imagenes -->
        <div class="grid grid-cols-3 gap-4">
            @foreach ($this->allProductImages as $image)
                <div class="border rounded overflow-hidden">
                    <img 
                        src="{{ $image['thumbnail'] }}" 
                        class="w-full h-32 object-cover"
                        onerror="this.src='{{ $image['original'] }}'"
                    >
                </div>
            @endforeach
        </div>
    </div>

    <!-- info del producto -->
    <div>
        <h1 class="text-3xl font-medium">{{ $this->product->name }}</h1>
        
        <!-- precio con/sin descuento-->
        <div class="text-xl text-gray-700">
            @if($discountApplied)
                <span class="line-through text-red-500">
                    ${{ number_format($originalPrice/100) }}
                </span>
                <span class="ml-2 text-green-600">
                    ${{ number_format($finalPrice/100) }}
                </span>
                @else
                    ${{ number_format($originalPrice/100) }}
            @endif
        </div>

        <div class="mt-4">
            {{ $this->product->description }}
        </div>

        <!-- stock disponible -->
        <div class="mt-2 text-sm {{ $this->availableStock > 0 ? 'text-green-600' : 'text-red-500' }}">
            @if($this->availableStock > 0)
                {{ $this->availableStock }} disponibles
                @if($this->availableStock <= $this->product->low_stock_threshold)
                    (¡Últimas unidades!)
                @endif
            @else
                Agotado
            @endif
        </div>

        <!-- formulario de cupón -->
        @unless($this->product->total_product_stock < 0)
            <livewire:coupon-form 
                context="product" 
                :targetId="$productId"
            />
        @endunless
        
        <!-- Selector de variante y checkout -->
        <div class="mt-4 space-y-4">
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

            <!-- boton de compra -->
            <button
                class="button"
                data-type="cart"
                wire:click="addToCart"
                @disabled($this->availableStock < 1)
            >
                {{ $this->availableStock > 0 ? 'Añadir al carrito' : 'AGOTADO' }}
            </button>

            @error('variant')
                <p class="mt-2 text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
