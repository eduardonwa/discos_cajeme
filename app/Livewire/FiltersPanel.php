<?php

namespace App\Livewire;

use Livewire\Attributes\Modelable;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Collection;
use App\Models\Product;

class FiltersPanel extends Component
{
    public Collection $collection;
    #[Modelable]
    public array $filters = [];
    public array $groups = [];

    public function mount(Collection $collection, ?array $groups = null): void
    {
        $this->collection = $collection;
        $this->filters = $this->filters ?? [];
        $this->groups = $groups ?? $this->buildVariantGroups($collection);
    }

    protected function buildVariantGroups(Collection $collection): array
    {
        
        $rows = Product::query()
            ->published()
            ->whereHas('collections', fn($q) => $q->whereKey($collection->id))
            ->join('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->join('attribute_variants', 'attribute_variants.product_variant_id', '=', 'product_variants.id')
            ->join('attributes', 'attributes.id', '=', 'attribute_variants.attribute_id')
            ->select('attributes.key as key', 'attribute_variants.value as value')
            ->distinct()
            ->orderBy('attributes.key')
            ->orderBy('attribute_variants.value')
            ->get();

        $groups = [];
        foreach ($rows as $r) {
            $k = $r->key;
            $groups[$k]['key'] = $k;
            $groups[$k]['values'][] = $r->value;
        }
        foreach ($groups as &$g) {
            $g['values'] = array_values(array_unique($g['values']));
        }
        return array_values($groups);
    }

    public function toggleFilter(string $bindKey, string $value): void
    {
        $bindKey = Str::slug($bindKey, '_');
        $current = $this->filters[$bindKey] ?? [];
        $idx = array_search($value, $current, true);

        if ($idx !== false) {
            unset($current[$idx]);
            $current = array_values($current);
        } else {
            $current[] = $value;
        }

        $this->filters[$bindKey] = $current;
        $this->dispatch('filters-updated');
    }

    public function clearGroup(string $bindKey): void
    {
        $this->filters[Str::slug($bindKey, '_')] = [];
        $this->dispatch('filters-updated');
    }

    public function clearAll(): void
    {
        $this->filters = [];
        $this->dispatch('filters-updated');
    }

    public function render()
    {
        return view('livewire.filters-panel');
    }
}
