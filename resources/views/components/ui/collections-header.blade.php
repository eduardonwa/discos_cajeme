<nav>
    <ul class="product-collections">
        @foreach ($productCollections as $collection)
            <li class="no-list-style">
                <a href="{{ route('collection', $collection->slug) }}">
                    {{ $collection->name }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>