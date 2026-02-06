<div class="above-fold"
>
    <livewire:hero-slider :slides="$heroSlider" wire:key="hero-slider" />

    <x-home-collections :collections="$collections" :activeTab="$activeTab" />

    <x-latest-products :variants="$variants" />

    <x-home-cta :cta="$cta" :ctaImage="$ctaImage" />

    <x-spotlight :spotlight="$spotlight" />

    <x-home-collections-rail :railCollections="$railCollections" />
    
    {{-- <div class="container" data-type="wide">
        <x-collections-carousel :collection="$verano" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$onSale" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$atemporal" type="full-price" />
        <x-collections-carousel :collection="$comodidad" type="full-price" />
    </div> --}}
</div>