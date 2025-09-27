<section class="product__media"
  x-data="{
    current:  @js($featured['large']),
    selected: 'featured',
    pick(url,id){ this.current = url; this.selected = id; }
  }"
>
  <div class="featured-img">
    <img :src="current" alt="{{ $name }}">
  </div>

  <div class="track">
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
</section>
