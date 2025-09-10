<div class="section">
    <div>
        <x-input wire:model.live.debounce="searchQuery" type="text" placeholder="Escribe algo"/>
        <div wire:loading.delay.shorter wire:target="searchQuery">Buscando...</div>
    </div>

    @if (filled($this->searchQuery))
        @if($this->productsQuery->count())
            <div>
                {{-- tarjeta de producto --}}
                @foreach ($this->productsQuery as $product)
                    <article>
                        <a
                            wire:navigate
                            href="{{ route('product', $product) }}"
                        >
                            <img
                                src="{{ $product->getFirstMediaUrl('images') }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                            >
                            <h2>{{ $product->name }}</h2>
                            <p>{{ $product->price }}</p>
                        </a>
                    </article> 
                @endforeach
            </div>

            {{-- paginacion --}}
            @if ($this->productsQuery->hasPages())
                <div>{{ $this->productsQuery->links() }}</div>
            @endif

            {{-- resultados --}}
            @else
                <p>{{ $this->searchQuery }}</p>
        @endif
    @endif

    <div class="container" data-type="wide">
        @if ($verano)
            <article class="collection">
                <header class="collection__header">
                    <a href="{{ route('collection', $verano) }}" class="clr-primary-800 no-decor uppercase ff-bold fs-700">{{ $verano->name }}</a>
                </header>
    
                <div class="collection__slider">
                    @foreach ($verano->products as $product)
                        <a
                            class="slide"
                            wire:navigate
                            href="{{ route('product', $product) }}"
                        >
                            <span class="slide__badge">40%</span>
                            <img class="slide__image" src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}">
                            <div class="slide__meta">
                                <h2 class="product">{{ $product->name }}</h2>
                                <p class="price">{{ $product->price }}</p>
                            </div>
                        </a>
                    @endforeach
                    <a class="button" data-type="mas" wire:navigate href="{{ route('collection', $verano) }}">Ver más</a>
                </div>
            </article>
        @endif

        @if ($onSale)
            <article class="collection">
                <header class="collection__header">
                    <a href="{{ route('collection', $onSale) }}" class="clr-primary-800 no-decor uppercase ff-bold fs-700">{{ $onSale->name }}</a>
                </header>
    
                <div class="collection__slider">
                    @foreach ($onSale->products as $product)
                        <a
                            class="slide"
                            wire:navigate
                            href="{{ route('product', $product) }}"
                        >
                            <span class="slide__badge">40%</span>
                            <img class="slide__image" src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}">
                            <div class="slide__meta">
                                <h2 class="product">{{ $product->name }}</h2>
                                <p class="price">{{ $product->price }}</p>
                            </div>
                        </a>
                    @endforeach
                    <a class="button" data-type="mas" wire:navigate href="{{ route('collection', $onSale) }}">Ver más</a>
                </div>
            </article>
        @endif

        @if ($atemporal)
            <article class="collection" data-type="full-price">
                <header class="collection__header">
                    <a href="{{ route('collection', $atemporal) }}" class="clr-primary-800 no-decor uppercase ff-bold fs-700">{{ $atemporal->name }}</a>
                    <div class="slide-buttons">
                        <x-icon data-type="arrow" data-type="arrow" orientation="left">
                            <x-ui.icons.arrow />
                        </x-icon>
                        <x-icon data-type="arrow">
                            <x-ui.icons.arrow />
                        </x-icon>
                    </div>
                </header>
    
                <div class="collection__slider">
                    @foreach ($atemporal->products as $product)
                        <article class="slide">
                            <a
                                class="slide__link | no-decor"
                                wire:navigate
                                href="{{ route('product', $product) }}"
                            >
                                <div class="slide__media">
                                    <img class="slide__image" src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}">
                                </div>    
                                <div class="slide__meta">
                                    <h2 class="product">{{ $product->name }}</h2>
                                    <p class="price">{{ $product->price }}</p>
                                </div>
                            </a>
                            <button
                                class="button"
                                data-type="cart"
                                type="button"
                                wire:click.prevent="addToCart({{ $product->id }})"
                                aria-label="Agregar {{ $product->name }} al carrito"
                            >
                                Agregar al carrito
                            </button>
                        </article>
                    @endforeach
                </div>
            </article>
        @endif

        @if ($comodidad)
            <article class="collection" data-type="full-price">
                <header class="collection__header">
                    <a href="{{ route('collection', $comodidad) }}" class="clr-primary-800 no-decor uppercase ff-bold fs-700">{{ $comodidad->name }}</a>
                    <div class="slide-buttons">
                        <x-icon data-type="arrow" data-type="arrow" orientation="left">
                            <x-ui.icons.arrow />
                        </x-icon>
                        <x-icon data-type="arrow">
                            <x-ui.icons.arrow />
                        </x-icon>
                    </div>
                </header>
    
                <div class="collection__slider">
                    @foreach ($comodidad->products as $product)
                        <article class="slide">
                            <a
                                class="slide__link | no-decor"
                                wire:navigate
                                href="{{ route('product', $product) }}"
                            >
                                <div class="slide__media">
                                    <img class="slide__image" src="{{ $product->getFirstMediaUrl('images') }}" alt="{{ $product->name }}">
                                </div>    
                                <div class="slide__meta">
                                    <h2 class="product">{{ $product->name }}</h2>
                                    <p class="price">{{ $product->price }}</p>
                                </div>
                            </a>
                            <button
                                class="button"
                                data-type="cart"
                                type="button"
                                wire:click.prevent="addToCart({{ $product->id }})"
                                aria-label="Agregar {{ $product->name }} al carrito"
                            >
                                Agregar al carrito
                            </button>
                        </article>
                    @endforeach
                </div>
            </article>
        @endif
    </div>
</div>
