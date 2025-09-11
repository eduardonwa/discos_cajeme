<div class="section">
    <div class="container" data-type="wide">
        <h2>{{ $collection->name }}</h2>
    
        @foreach ($products as $product)
            <article>
                <h2>{{ $product->name }}</h2>
                <p>{{ $product->price }}</p>
            </article>
        @endforeach
    </div>
</div>
