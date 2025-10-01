@props(['title' => false])
<div {{ $attributes->class(['cart__wrapper']) }}>
    @if($title)
        <h2 class="title">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>