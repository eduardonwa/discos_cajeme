<div class="above-fold"
>
    <livewire:hero-slider :slides="$heroSlider" wire:key="hero-slider" />

    <x-home-collections :collections="$collections" :activeTab="$activeTab" />

    {{-- <div class="container" data-type="wide">
        <x-collections-carousel :collection="$verano" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$onSale" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$atemporal" type="full-price" />
        <x-collections-carousel :collection="$comodidad" type="full-price" />
    </div> --}}
</div>