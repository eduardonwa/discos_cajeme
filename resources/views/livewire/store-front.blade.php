<div class="above-fold"
>
    <livewire:hero-slider :slides="$heroSlider" wire:key="hero-slider" />

    <div class="flex gap-2">
        @foreach ($collections as $collection)
            <button
                wire:key="tab-{{ $collection->id }}"
                wire:click="setActiveTab('{{ $collection->slug }}')"
                class="{{ $activeTab === $collection->slug ? 'fw-bold' : '' }}"
                type="button"
            >
                {{ $collection->name }}
            </button>
        @endforeach
    </div>

    @foreach ($collections as $collection)
        @if ($activeTab === $collection->slug)
            <ul wire:key="panel-{{ $collection->id }}">
                @foreach ($collection->products as $product)
                    <li>{{ $product->artist }} {{ $product->name }}</li>
                @endforeach
            </ul>
        @endif
    @endforeach

    {{-- <div class="container" data-type="wide">
        <x-collections-carousel :collection="$verano" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$onSale" type="rebajas" :showMore="true" />
        <x-collections-carousel :collection="$atemporal" type="full-price" />
        <x-collections-carousel :collection="$comodidad" type="full-price" />
    </div> --}}
</div>