<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Collection;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class Collections extends Component
{
    use WithPagination;

    public Collection $collection;
    public string $search = '';
    public int $perPage = 12;
    public array $filters = [];
    public int $filtersKey = 0;

    public function mount(Collection $collection)
    {
        abort_unless($collection->is_active, 404);
        $this->collection = $collection;
    }

    public function updatingFilters() { $this->resetPage(); }

    public function toggleFilter(string $bindKey, string $value): void
    {
        $bindKey = Str::slug($bindKey, '_');

        $current = $this->filters[$bindKey] ?? [];
        $idx = array_search($value, $current, true);

        if ($idx !== false) {
            // quitar
            unset($current[$idx]);
            $current = array_values($current);
        } else {
            // agregar
            $current[] = $value;
        }

        $this->filters[$bindKey] = $current;
        $this->resetPage();
    }

    public function clearGroup(string $bindKey): void
    {
        $bindKey = \Illuminate\Support\Str::slug($bindKey, '_');
        $this->filters[$bindKey] = [];
        $this->filtersKey++;
    }

    public function clearAll(): void
    {
        $this->filters = [];
        $this->filtersKey++;
    }

    public function render()
    {
        $query = Product::query()
            ->published()
            ->whereHas('collections', fn($q) => $q->whereKey($this->collection->id));
        
        foreach ($this->filters as $bindKey => $values) {
            $values = array_values(array_filter((array)$values, fn($v) => $v !== '' && $v !== null));
            if (!empty($values)) {
                $query->whereHas('variants.attributes', function ($q) use ($bindKey, $values) {
                    $q->whereIn('attribute_variants.value', $values)
                      ->whereHas('attribute', function ($aq) use ($bindKey) {
                          $aq->whereRaw('REPLACE(LOWER(`key`), " ", "_") = ?', [Str::slug($bindKey, '_')]);
                      });
                });
            }
        }
        
        $products = $query
            ->with(['media','collections'])
            ->orderByDesc('products.created_at')
            ->paginate($this->perPage);

        return view('livewire.collections-page', compact('products'));
    }
}