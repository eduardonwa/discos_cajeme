@if ($spotlight)
    <div class="spotlight | container" data-type="wide">
        <h2 class="eyebrow">{{ $spotlight['header'] }}</h2>
        
        <div class="spotlight__shell">
            <div class="info | flow">
                <p class="title">{{ $spotlight['title'] }}</p>
                <p class="description">{{ $spotlight['description'] }}</p>

                @if(!empty($spotlight['tags']))
                    <div class="tags">
                        @foreach ($spotlight['tags'] as $tag)
                            <div class="tag">
                                <div class="u-media u-media--icon">
                                    <img
                                        class="u-media__img"
                                        src="{{ asset('storage/' . $tag['icon']) }}"
                                        alt="{{ $tag['label'] }}"
                                        loading="lazy"
                                    >
                                </div>

                                <div class="tag__info">
                                    <p class="label">{{ $tag['label'] }}</p>
                                    <p class="description">{{ $tag['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
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