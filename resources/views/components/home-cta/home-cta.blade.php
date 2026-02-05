<div class="container" data-type="wide">
    <section class="home-cta">
        <div class="u-media u-media--landscape">
            <img
                class="u-media__img"
                src="{{ $ctaImage }}"
                alt="{{ $cta['bg_img_alt'] }}"
            >
        </div>

        <div class="overlay"></div>

        <div class="home-cta__content | flow">
            <h2 class="heading-3">{{ $cta['header'] }}</h2>
            <p>{{ $cta['description'] }}</p>
            <a href="{{ $cta['button_link'] }}" class="button">
                {{ $cta['button'] }}
            </a>
        </div>
    </section>
</div>