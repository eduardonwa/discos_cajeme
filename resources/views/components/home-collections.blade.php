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
                <div class="content"
                    wire:key="prod-{{ $active->slug }}-{{ $product->id }}"
                >
                    <a class="no-decor" href="{{ route('product', $product->slug) }}">
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
                    </a>

                    <div class="actions">
                        <x-icon
                            wire:click="addToCart({{ $product->id }})"
                            wire:key="add-{{ $active->slug }}-{{ $product->id }}"
                            data-type="add-to-cart"
                        >
                            <x-ui.icons.cart />
                        </x-icon>

                        <button class="button" data-type="buy-now">
                            Comprar ahora
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
