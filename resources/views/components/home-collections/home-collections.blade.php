<div class="home-collections | container" data-type="wide">
    
    <h2 class="heading-2">{{ $this->collectionHeader }}</h2>

    <div class="home-collections__tabs">
        <div class="buttons">
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
                        </div>
                    </a>

                    <div class="actions">
                        <a href="{{ route('product', $product->slug) }}" class="button" data-type="buy-now">
                            Ver producto
                        </a>
                    </div>
                </div>
                </button>
            @endforeach

            <a href="{{ route('collection', $active->slug) }}" class="button" data-type="lowercase">
                Ver toda la colección
            </a>
        </div>
    @endif
</div>
