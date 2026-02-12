<div class="price">
    @if ($originalPrice)
        <span class="line-through">@money($originalPrice)</span>
        <span>@money($finalPrice)</span>
    @else
        <span>@money($finalPrice)</span>
    @endif
</div>