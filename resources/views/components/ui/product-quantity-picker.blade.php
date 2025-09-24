@php
  $quantity = $quantity ?? 1;
  $max      = max(1, $max ?? 1);
  $prop     = $prop ?? 'quantity';
  $label    = $label ?? 'Cantidad';
  $disabled = (bool) ($disabled ?? false);
@endphp

<div
  class="quantity"
  x-data="{
    open:false,
    choose(n){ $wire.set('{{ $prop }}', n); this.open=false; }
  }"
>
  <button
    type="button"
    class="quantity-trigger"
    @click="if(!{{ $disabled ? 'true' : 'false' }}) open = true"
    @disabled($disabled)
    aria-haspopup="dialog"
    :aria-expanded="open"
  >
    <span>{{ $label }}: {{ $quantity }}</span>
    <svg width="16" height="16" viewBox="0 0 20 20" aria-hidden="true"><path d="M5 7l5 6 5-6"/></svg>
  </button>

  <div
    class="quantity-modal"
    x-show="open"
    x-cloak
    x-transition
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="open=false"
  >
    <div class="quantity-modal__backdrop" @click.self="open=false"></div>

    <div class="quantity-modal__content" x-trap.noscroll="open" @click.stop>
      <div class="controls">
        <h2 class="ff-semibold fs-500">{{ $label }}:</h2>
        <x-icon @click="open=false" aria-label="Cerrar">
            <x-ui.icons.close />
        </x-icon>
      </div>

      <ul class="list-options" role="listbox" aria-label="{{ $label }}">
        @for ($i = 1; $i <= $max; $i++)
          <li>
            <button
              type="button"
              @click="choose({{ $i }})"
              class="list-options__button {{ $quantity === $i ? 'list-options__button--checked' : '' }}"
              role="option"
              aria-selected="{{ $quantity === $i ? 'true' : 'false' }}"
            >
              {{ $i }}
            </button>
          </li>
        @endfor
      </ul>
    </div>
  </div>
</div>
