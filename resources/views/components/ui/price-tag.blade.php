<div class="price">
    @if ($originalPrice)
        <span class="price__original">{{ $originalPrice }}</span>
        <span class="price__final">{{ $finalPrice }}</span>
    @else
        <span class="price__final">{{ $finalPrice }}</span>
    @endif
</div>