@if ($this->hasCta)
    <section class="home-cta">
        <div class="home-cta__content">
            <h2 class="heading-2">{{ $cta['header'] }}</h2>
            <p class="description">{{ $cta['description'] }}</p>
            <a href="{{ $cta['button_link'] }}" class="button" data-type="cta">
                {{ $cta['button'] }}
            </a>
        </div>
    </section>
@endif