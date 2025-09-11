@props([
  'product',
  'href' => null,
  'variant' => 'vertical',
  'badge' => null,
])

<article {{ $attributes->class([
  'card',
  "card--{$variant}",
]) }}>
  @if ($href)
  <a class="card__link no-decor" wire:navigate href="{{ $href }}">
  @endif

    <div class="card__media">
      <img class="image"
           src="{{ $product->getFirstMediaUrl('images') }}"
           alt="{{ $product->name }}">
      @if ($badge)
        <span class="badge">{{ $badge }}</span>
      @endif
    </div>

    <div class="card__meta">
      <h3 class="heading">{{ $product->name }}</h3>
      <p class="price">{{ $product->price }}</p>
    </div>

  @if ($href)
  </a>
  @endif
</article>
