@php $slide = $slides[$active] ?? null; @endphp

<div class="hero-slider">
    @if($slide)
        <section class="hero-slider__frame" aria-label="Hero principal">
            <a href="{{ $slide['link'] }}" class="img-shell" aria-hidden="true">
                <img
                    class="image"
                    src="{{ $slide['src'] }}"
                    @if(!empty($slide['srcset'])) srcset="{{ $slide['srcset'] }}" @endif
                    sizes="{{ $slide['sizes'] ?? '100vw' }}"
                    alt="{{ $slide['image_alt'] ?? '' }}"
                    loading="eager"
                />
            </a>

            @if(count($slides) > 1)
                <nav class="hero-slider__controls" aria-label="Controles de hero">
                    <div class="dots" role="tablist" aria-label="Slides">
                        @foreach ($slides as $i => $s)
                            <button
                                type="button"
                                class="button {{ $i === $active ? 'is-active' : '' }}"
                                data-type="hero-dot"
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
