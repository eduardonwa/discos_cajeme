<div class="section">
    <div class="container" data-type="wide">
        {{-- info --}}
        <h2>{{ $collection->name }}</h2>
        <p>{{ $collection->description }}</p>
        
        <div wire:key="filters-wrap-{{ $filtersKey }}">
            @include('components.ui.collections-filter', [
                'collection' => $collection,
                'filters'    => $filters,
            ])
        </div>

        {{-- productos --}}
        @foreach ($products as $product)
            <article>
                <h2>{{ $product->name }}</h2>
                <p>{{ $product->price }}</p>
            </article>
        @endforeach
    </div>
</div>
