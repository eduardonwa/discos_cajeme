@if ($product)
    <div style="width: 100%; aspect-ratio: 16 / 9; overflow: hidden;">
        <img
            src="{{ $product->getFirstMediaUrl('featured', 'thumb') ?: $product->getFirstMediaUrl('images', 'thumb') }}"
            alt="{{ $product->name }}"
            style="height: 100%; width: 100%; object-fit: contain; object-position: center 20%;"
        />
    </div>
@endif