<section class="product__media"
  x-data="{
    {{-- estado de la galeria --}}
    current:  @js($featured['large']),
    selected: 'featured',
    pick(url,id){ this.current = url; this.selected = id; },
    {{-- manejo de thumbnails --}}
    skip: 2,
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
    }
  }"
>
  <div class="featured-img">
    <img :src="current" alt="{{ $name }}">
  </div>

  <div class="track-wrap">
    <x-icon @click="prev()" class="prev" data-type="arrow" orientation="left">
      <x-ui.icons.arrow />
      <span class="sr-only">Ir a la izquierda</span>
    </x-icon>

    <div class="track" x-ref="slider" tabindex="0" role="listbox">
      <div class="track__item"
           role="button" tabindex="0"
           @click="pick(@js($featured['large']), 'featured')"
           @keydown.enter.prevent="pick(@js($featured['large']), 'featured')"
           @keydown.space.prevent="pick(@js($featured['large']), 'featured')"
           :class="{ 'is-checked': selected==='featured' }"
           :aria-pressed="selected==='featured'">
        <img src="{{ $featured['thumb'] }}" alt="{{ $name }}">
      </div>
      @foreach ($images as $index => $image)
        @php $thumbId = 'img-'.$index; @endphp
        <div class="track__item"
             role="button" tabindex="0"
             @click="pick(@js($image['original']), @js($thumbId))"
             @keydown.enter.prevent="pick(@js($image['original']), @js($thumbId))"
             @keydown.space.prevent="pick(@js($image['original']), @js($thumbId))"
             :class="{ 'is-checked': selected === @js($thumbId) }"
             :aria-pressed="selected === @js($thumbId)">
          <img src="{{ $image['thumbnail'] }}" alt="{{ $name }}" onerror="this.src='{{ $image['original'] }}'">
        </div>
      @endforeach
    </div>

    <x-icon @click="next()" class="next" data-type="arrow" orientation="right">
      <x-ui.icons.arrow />
      <span class="sr-only">Ir a la derecha</span>
    </x-icon>
  </div>
</section>
