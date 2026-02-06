<div class="container" data-type="wide">
    @if(!empty($railCollections['collections']))
        <section class="home-collections-rail">

            <div class="home-collections-rail__info">
                <div class="content">
                    <h2 class="heading-2">{{ $railCollections['header'] }}</h2>
                    <p>{{ $railCollections['description'] }}</p>
                </div>

                <nav class="controls" aria-label="Controles del carrusel de colecciones">
                    <button class="button" data-type="" aria-label="Anterior"><</button>
                    <button class="button" data-type="" aria-label="Siguiente">></button>
                </nav>
            </div>
    
            <div class="rail">
                @foreach($railCollections['collections'] as $col)
                    <div class="rail__item">
                        <div class="u-media u-media--square">
                            <img
                                class="u-media__img"
                                src="{{ $col['thumb']['md'] }}"
                                alt="{{ $col['name'] }}"
                            >
                        </div>
    
                        <div class="rail__cta">
                            <a class="button" data-type="secondary" href="{{ route('collection', ['collection' => $col['slug']]) }}">
                                {{ $col['name'] }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>