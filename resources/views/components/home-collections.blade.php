<div>
    <div class="flex flex-wrap gap-2">
        @foreach ($collections as $tab)
            <button
                type="button"
                wire:click="setActiveTab('{{ $tab->slug }}')"
                wire:key="tab-btn-{{ $tab->slug }}"
                class="{{ $activeTab === $tab->slug ? 'fw-600' : '' }}"
            >
                {{ $tab->name }}
            </button>
        @endforeach
    </div>

    @php
        $active = collect($collections)->firstWhere('slug', $activeTab);
    @endphp

    @if ($active)
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($active->products as $product)
                <div
                    wire:key="prod-{{ $active->slug }}-{{ $product->id }}"
                    class=""
                >
                    <div class="image-wrapper">
                        <img
                            class="image"
                            src="{{ $product->getFirstMediaUrl('featured', 'md_thumb') }}"
                            alt="{{ $product->name }}">
                    </div>
                 
                    <div class="">
                        <p>{{ $product->name }}</p>
                        <p>{{ $product->price }}</p>
                    </div>

                    <div class="actions">
                        <button
                            type="button"
                            wire:click="addToCart({{ $product->id }})"
                            wire:key="add-{{ $active->slug }}-{{ $product->id }}"
                            class="button"
                        >
                            Agregar al carrito
                        </button>

                        <button class="button">
                            Comprar ahora
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
