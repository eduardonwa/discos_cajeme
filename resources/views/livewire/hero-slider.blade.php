@php
    $slide = $slides[$active] ?? null;

    $youtubeId = null;

    if ($slide && ($slide['media_type'] ?? null) === 'video' && !empty($slide['video_url'])) {
        parse_str(parse_url($slide['video_url'], PHP_URL_QUERY) ?? '', $q);
        $youtubeId = $q['v'] ?? null;

        // por si usan youtu.be/XXXX
        if (!$youtubeId) {
            $path = trim(parse_url($slide['video_url'], PHP_URL_PATH) ?? '', '/');
            $youtubeId = $path ?: null;
        }
    }

    $youtubeParams = $youtubeId ? http_build_query([
        'autoplay' => 1,
        'mute' => 1,
        'controls' => 0,
        'rel' => 0,
        'playsinline' => 1,
        'loop' => 1,
        'playlist' => $youtubeId,
        'modestbranding' => 1,
    ]) : '';
@endphp

<div>
    @if($slide)
        <section class="hero" aria-label="Hero principal">
            <div class="hero_background" aria-hidden="true">
                @if(($slide['media_type'] ?? '') === 'image')
                    <img
                        class="image"
                        src="{{ $slide['image_url'] ?? '' }}"
                        alt="{{ $slide['image_alt'] ?? ''}}"
                        loading="eager"
                    />
                @elseif(($slide['media_type'] ?? '') === 'video' && $youtubeId)
                    <div class="hero_video">
                        <iframe
                            class="video-iframe"
                            src="https://www.youtube.com/embed/{{ $youtubeId }}?{{ $youtubeParams }}"
                            title="Video de fondo"
                            frameborder="0"
                            allow="autoplay; encrypted-media;"
                            allowfullscreen
                        ></iframe>
                    </div>
                @endif

                <div class="overlay"></div>
            </div>

            @if(count($slides) > 1)
                <nav class="hero__controls" aria-label="Controles de hero">
                    <div class="dots" role="tablist" aria-label="Slides">
                        @foreach ($slides as $i => $s)
                            <button
                                type="button"
                                class="hero-dot {{ $i === $active ? 'is-active' : '' }}"
                                wire:click="goTo({{ $i }})"
                                aria-label="Ir al slide {{ $i + 1 }}"
                                aria-current="{{ $i === $active ? 'true' : 'false' }}"
                            ></button>
                        @endforeach
                    </div>
                </nav>
            @endif
        </section>
    @endif
</div>
