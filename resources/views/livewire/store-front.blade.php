<div class="above-fold"
>
    <livewire:hero-slider :slides="$heroSlider" wire:key="hero-slider" />

    @foreach ($collections as $collection)
        <a href="{{ route('collection', $collection->slug) }}">
            <h2>{{ $collection->name }}</h2>
        </a>

        @foreach ($collection->products as $product)
            <div>{{ $product->name }}</div>
        @endforeach
    @endforeach

    
    
    {{-- <div class="container" data-type="wide">
        <x-collections-carousel :collection="$verano" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$onSale" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$atemporal" type="full-price" />
        <x-collections-carousel :collection="$comodidad" type="full-price" />
    </div> --}}
</div>