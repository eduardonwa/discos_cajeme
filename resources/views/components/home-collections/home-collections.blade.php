<div class="home-collections | container" data-type="wide">
    
    <h2 class="heading-2">{{ $this->collectionHeader }}</h2>

    <div class="home-collections__tabs">
        <div class="buttons">
            @foreach ($collections as $tab)
                <span
                    type="button"
                    wire:click="setActiveTab('{{ $tab->slug }}')"
                    wire:key="tab-btn-{{ $tab->slug }}"
                    class="badge {{ $activeTab === $tab->slug ? 'badge--active' : '' }}"
                    data-type="h-collection"
                >
                    {{ $tab->name }}
                </span>
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
                    <div class="info">
                        <p class="name">{{ $product->name }}</p>
                    </div>

                    <div class="u-media u-media--square">
                        <img
                            class="u-media__img"
                            src="{{ $product->getFirstMediaUrl('featured', 'md_thumb') }}"
                            alt="{{ $product->name }}">
                    </div>
                    
                    <div class="actions">
                        <a href="{{ route('product', $product->slug) }}" class="button" data-type="primary">
                            Ver producto
                        </a>
                    </div>
                </div>
            @endforeach

            <a href="{{ route('collection', $active->slug) }}" class="button" data-type="outline">
                Ver toda la colección
            </a>
        </div>
    @endif
</div>
