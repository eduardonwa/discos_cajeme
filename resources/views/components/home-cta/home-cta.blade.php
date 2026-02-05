<div class="container" data-type="wide">
    <div class="u-media">
        <img
            class="u-media__img"
            src="{{ $ctaImage }}"
            alt="{{ $cta['bg_img_alt'] }}"
        >
    </div>
    <h2>{{ $cta['header'] }}</h2>

    <a href="{{ $cta['button_link'] }}" class="button">
        {{ $cta['button'] }}
    </a>
</div>