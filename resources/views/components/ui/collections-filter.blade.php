@foreach ($facets as $key => $options)
    @php
        $bindKey = \Illuminate\Support\Str::slug($key, '_');
        $groupStateHash = md5(json_encode($filters[$bindKey] ?? []));
    @endphp

    <fieldset wire:key="facet-group-{{ $collection->id }}-{{ $bindKey }}-{{ $groupStateHash }}">
        <legend class="uppercase">{{ strtoupper($key) }}</legend>

        @if (!empty($filters[$bindKey] ?? []))
            <button type="button" data-type="outline" wire:click="clearGroup('{{ $bindKey }}')">
                Limpiar {{ $key }}
            </button>
        @endif

        @foreach ($options as $opt)
            @php
                $valRaw = $opt['value'] ?? '';
                $val = is_string($valRaw) ? trim($valRaw) : (string) $valRaw;
            @endphp
            @if ($val === '') @continue @endif

            <label wire:key="facet--{{ $collection->id }}-{{ $bindKey }}-{{ md5($val) }}">
                <input
                    type="checkbox"
                    wire:click="toggleFilter('{{ $bindKey }}','{{ addslashes($val) }}')"
                    @checked(in_array($val, $filters[$bindKey] ?? [], true))
                >
                <span>{{ $opt['value'] }} ({{ $opt['count'] ?? 0}})</span>
            </label>
        @endforeach
    </fieldset>
@endforeach
