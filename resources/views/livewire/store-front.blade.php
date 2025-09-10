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
                    <a class="button" data-type="button" wire:navigate href="{{ route('collection', $verano) }}">Ver más</a>
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
                    <a class="button" data-type="button" wire:navigate href="{{ route('collection', $onSale) }}">Ver más</a>
                </div>
            </article>
        @endif
    </div>
{{--     <div class="flex justify-between">
        <h1 class="text-xl ff-base uppercase">Our Products</h1>
        <div>
            <x-input wire:model.live.debounce="searchQuery" type="text" placeholder="Escribe algo"/>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mt-12">
        <p>{{ now()->isoFormat('DD MMMM YYYY') }}</p>
        @foreach ($this->productsQuery as $query)
            <x-order-panel class="relative">
                <a
                    wire:navigate
                    href="{{ route('product', $product) }}"
                    class="absolute inset-0 w-full h-full"
                >
                    <img src="{{ $product->getFirstMediaUrl('featured', 'md_thumb') }}" class="rounded" alt="">
                    <h2 class="font-medium text-lg">{{ $product->name }}</h2>
                    <span class="text-gray-700 text-sm">{{ $product->price }}</span>
                </a>
            </x-order-panel>
        @endforeach
    </div>
    
    <div class="mt-6">
        {{ $this->products->links() }}
    </div> --}}
</div>
