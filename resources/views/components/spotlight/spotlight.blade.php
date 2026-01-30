@if ($spotlight)
    <div class="spotlight | container" data-type="wide">
        <h2 class="eyebrow">{{ $spotlight['header'] }}</h2>
        
        <div class="spotlight__shell">
            <div class="info">
                <p class="title">{{ $spotlight['title'] }}</p>
                <p class="description">{{ $spotlight['description'] }}</p>
            </div>
    
            <div class="u-media">
                <img
                    class="u-media__img"
                    src="{{ $spotlight['product']->getFirstMediaUrl('featured', 'lg_thumb') }}"
                    alt="{{ $spotlight['product']['cover_img_alt'] }}"
                    loading="lazy"
                >
            </div>
        </div>
    </div>
@endif