@props([
  'size' => 24,
  'label' => null,
  'color' => '#344D55',
  'decorative' => false,
  'href' => null,
  'orientation' => 'right',
])

@php
  $sizeValue = is_numeric($size) ? $size.'px' : $size;
  $dir = $orientation === 'left' ? 'left' : 'right';
@endphp

@if($href)
  <a href="{{ $href }}" class="ui-icon-btn" aria-label="{{ $label }}" target="_blank">
    <svg
      {{ $attributes
          ->merge(['class' => 'ui-icon'])
          ->merge($decorative ? ['aria-hidden' => 'true', 'role' => 'img'] : ['role' => 'img', 'aria-label' => $label])
      }}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      style="--icon-size: {{ $sizeValue }};"
      class="ui-icon"
    >
      {{ $slot }}
    </svg>
  </a>
@else
  <button
    {{ $attributes->merge(['class' => 'ui-icon-btn']) }}
    aria-label="Buscar"
    type="button"
  >
    <svg
      {{ $attributes
          ->merge(['class' => "ui-icon $dir"])
          ->merge($decorative ? ['aria-hidden' => 'true', 'role' => 'img'] : ['role' => 'img', 'aria-label' => $label])
      }}
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      style="--icon-size: {{ $sizeValue }};"
      class="ui-icon"
    >
      {{ $slot }}
    </svg>
  </button>
@endif