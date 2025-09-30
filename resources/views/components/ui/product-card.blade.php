@props([
  'product',
  'href' => null,
  'variant' => 'vertical',
  'badge' => null,
])

<article {{ $attributes->class([
  'card',
  "card--{$variant}",
  ]) }}
>
  @if ($href)
    <a class="card__link no-decor" wire:navigate href="{{ $href }}">
  @endif

  <div class="card__media">
    <img
      class="image"
      src="{{ $product->getFirstMediaUrl('featured') }}"
      alt="{{ $product->name }}"
  >
    @if ($badge)
      <span class="badge">{{ is_numeric($badge) ? $badge.'%' : $badge }}</span>
    @endif
  </div>

  <div class="card__meta">
    <h3 class="heading">{{ $product->name }}</h3>
    <x-ui.price-tag 
      :finalPrice="$product->final_price" 
      :originalPrice="$product->original_price" 
    />
  </div>

  @if ($href)
    </a>
  @endif
</article>
