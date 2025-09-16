@props([
    'collection' => null,
    'type' => 'full-price',
    'showMore' => false,
    'moreLabel' => 'Ver más',
])

@if ($collection)
  <article
    class="c-collection-rail"
    @if($type === 'full-price')
        data-type="full-price"
        x-data="{
            skip: 3,
            next() {
                this.to((current, offset) => current + (offset * this.skip))
            },
            prev() {
                this.to((current, offset) => current - (offset * this.skip))
            },
            to(strategy) {
                let slider = this.$refs.slider
                let current = slider.scrollLeft
                let offset = slider.firstElementChild.getBoundingClientRect().width

                slider.scrollTo({ left: strategy(current, offset), behavior: 'smooth' })
            },
            focusableWhenVisible: {
                'x-intersect:enter'() {
                    this.$el.removeAttribute('tabindex')
                },
                'x-intersect:leave'() {
                    this.$el.setAttribute('tabindex', '-1')
                },
            }
        }"    
    @endif
>
    <header class="c-collection-rail__header">
      <a href="{{ route('collection', $collection) }}"
        class="clr-primary-800 no-decor uppercase ff-bold fs-700"
      >
        {{ $collection->name }}
      </a>

      @if ($type === 'full-price')
        <div class="slide-buttons">
          <x-icon @click="prev()" data-type="arrow" orientation="left">
            <x-ui.icons.arrow />
            <span class="sr-only">Ir a la izquierda</span>
          </x-icon>
          
          <x-icon @click="next()" data-type="arrow" orientation="right">
            <x-ui.icons.arrow />
            <span class="sr-only">Ir a la derecha</span>
          </x-icon>
        </div>
      @endif
    </header>

    <div class="c-collection-rail__track" x-ref="slider" tabindex="0" role="listbox">
      @foreach ($collection->products as $product)
        @if ($type === 'rebajas')
          <div class="item">
            <x-ui.product-card
              :product="$product"
              :href="route('product', $product)"
              variant="overlay"
              :badge="$product->discount_percentage ?? 'REBAJA'"
            />
          </div>
        @else
          <div class="item">
            <x-ui.product-card
              :product="$product"
              :href="route('product', $product)"
              variant="vertical"
            />
          </div>
        @endif
      @endforeach

      @if ($type === 'rebajas' && $showMore)
        <a class="button" data-type="mas" wire:navigate href="{{ route('collection', $collection) }}">
          {{ $moreLabel }}
        </a>
      @endif
    </div>
  </article>
@endif