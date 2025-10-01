@props([
  'size' => 24,
  'label' => null,
  'decorative' => false,
  'href' => null,
  'orientation' => 'right',
  'fill' => null,
  'color' => null,
])

@php
  $sizeValue = is_numeric($size) ? $size.'px' : $size;
  $dir = $orientation === 'left' ? 'left' : 'right';
  $colorValue = $color ?? $fill;
  $styleInline = "--icon-size: {$sizeValue};" . ($colorValue ? " color: {$colorValue};" : "");
@endphp

@if($href)
  <a {{ $attributes->class('ui-icon-btn') }} aria-label="{{ $label }}" target="_blank" style="{{ $styleInline }}">
    <svg
      class="ui-icon {{ $dir }}"
      role="img"
      viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
      style="--icon-size: {{ $sizeValue }};"
      @if($decorative) aria-hidden="true" @else aria-label="{{ $label }}" @endif
    >
      {{ $slot }}
    </svg>
  </a>
@else
  <button type="button" {{ $attributes->class('ui-icon-btn') }} aria-label="{{ $label }}" style="{{ $styleInline }}">
    <svg
      class="ui-icon {{ $dir }}"
      role="img"
      viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
      style="--icon-size: {{ $sizeValue }};"
      @if($decorative) aria-hidden="true" @else aria-label="{{ $label }}" @endif
    >
      {{ $slot }}
    </svg>
  </button>
@endif
