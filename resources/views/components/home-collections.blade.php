<div class="home-collections | container" data-type="wide">
    <h2 class="heading-2">{{ $this->collectionHeader }}</h2>

    <div class="home-collections__tabs">
        @foreach ($collections as $tab)
            <button
                type="button"
                wire:click="setActiveTab('{{ $tab->slug }}')"
                wire:key="tab-btn-{{ $tab->slug }}"
                class="badge {{ $activeTab === $tab->slug ? 'active-tab' : '' }}"
                data-type="h-collection"
            >
                {{ $tab->name }}
            </button>
        @endforeach
    </div>

    @php
        $active = collect($collections)->firstWhere('slug', $activeTab);
    @endphp

    @if ($active)
        <div class="home-collections__results">
            @foreach ($active->products as $product)
                <a class="content | no-decor"
                    href="{{ route('product', $product->slug) }}"
                    wire:key="prod-{{ $active->slug }}-{{ $product->id }}"
                >
                    <div class="image-wrapper">
                        <img
                            class="image"
                            src="{{ $product->getFirstMediaUrl('featured', 'md_thumb') }}"
                            alt="{{ $product->name }}">
                    </div>
                 
                    <div class="info">
                        <p class="name">{{ $product->name }}</p>
                        <p class="price">{{ $product->price }}</p>
                    </div>

                    <div class="actions">
                        <button
                            type="button"
                            wire:click="addToCart({{ $product->id }})"
                            wire:key="add-{{ $active->slug }}-{{ $product->id }}"
                            class="button"
                            data-type="add-to-cart"
                        >
                            Agregar al carrito
                        </button>

                        <button class="button" data-type="buy-now">
                            Comprar ahora
                        </button>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
