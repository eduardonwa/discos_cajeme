<x-app-layout>
    <div class="all-collections | container" data-type="wide">
        <h2 class="heading-2">Colecciones</h2>
        
        <div class="all-collections__shell">
            @foreach ($collections as $collection)
                <div class="all-collections__item">
                    <a class="no-decor" href="{{ route('collection', $collection->slug) }}">
                        <h2 class="heading-3">{{ $collection->name }}</h2>
                        
                        <div class="u-media u-media__square">
                            <img
                                class="u-media__img"
                                src="{{ $collection->getFirstMediaUrl('col_thumbnail', 'md_thumb') }}"
                                alt="{{ $collection->name }}"
                            >
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>