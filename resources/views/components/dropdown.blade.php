@props([
    'align' => 'right',
    'width' => 'sm',
    'contentClasses' => 'dropdown__box__content',
    'dropdownClasses' => ''
])

@php
$alignmentClasses = match ($align) {
    'left'          => 'dropdown--left',
    'right'         => 'dropdown--right',
    'top'           => 'dropdown--top',
    'top-left'      => 'dropdown--top-left',
    'top-right'     => 'dropdown--top-right',
    'none', 'false' => 'dropdown--none',
    default         => 'dropdown--right',
};

$widthClasses = match ($width) {
    'sm' => 'dropdown--sm',
    'md' => 'dropdown--md',
    default => 'dropdown--sm',
};
@endphp

<div class="dropdown" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
        x-transition:enter.duration.200ms
        x-transition:enter-start.scale.95
        x-transition:enter-end.scale.100
        x-transition:leave.duration.75ms
        x-transition:leave-start.scale.100
        x-transition:leave-end.scale.95
        class="dropdown__box box-shadow-3 {{ $widthClasses }} {{ $alignmentClasses }} {{ $dropdownClasses }}"
        style="display: none;"
        @click="open = false"
    >
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
