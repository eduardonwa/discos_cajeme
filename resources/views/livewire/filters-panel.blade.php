<div class="filters-panel">
  @foreach ($groups as $group)
    @php
        $bindKey = \Illuminate\Support\Str::slug($group['key'], '_');
        $groupStateHash = md5(json_encode($filters[$bindKey] ?? [])); // fuerza re-render solo del grupo
    @endphp

    <fieldset class="filters-panel__options" wire:key="facet-group-{{ $collection->id }}-{{ $bindKey }}-{{ $groupStateHash }}">
      <legend class="variant-category | ff-semibold uppercase">{{ strtoupper($group['key']) }}</legend>

      @if (!empty($filters[$bindKey] ?? []))
        <button class="button" type="button" wire:click="clearGroup('{{ $bindKey }}')">
          Limpiar {{ $group['key'] }}
        </button>
      @endif

      @foreach ($group['values'] as $valRaw)
        @php
            $val = is_string($valRaw) ? trim($valRaw) : (string) $valRaw;
            if ($val === '') continue;
            $itemKey = md5($val);
        @endphp

        <label class="item" wire:key="facet--{{ $collection->id }}-{{ $bindKey }}-{{ $itemKey }}">
          <input
            type="checkbox"
            @checked(in_array($val, $filters[$bindKey] ?? [], true))
            wire:click="toggleFilter('{{ $bindKey }}', @js($val))"
          >
          <span>{{ $val }}</span>
        </label>
      @endforeach
    </fieldset>
  @endforeach

  <footer class="filters-panel__footer">
    <button type="button" class="button" data-type="ghost" wire:click="clearAll">Limpiar todo</button>
  </footer>
</div>
