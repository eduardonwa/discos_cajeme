<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class Collections extends Component
{
    use WithPagination;
    
    public ?Product $hero = null;
    public Collection $collection;
    public string $search = '';
    public int $perPage = 12;
    public array $filters = [];

    public function mount(Collection $collection)
    {
        abort_unless($collection->is_active, 404);
        $this->collection = $collection->load('featuredProduct');
        $this->hero = $this->collection->featuredProduct;
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    #[On('filters-updated')]
    public function refreshOnFiltersUpdated()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query()
            ->published()
            ->whereHas('collections', fn($q) =>
                $q->whereKey($this->collection->id)
            );

        // excluye el ID del "hero" del render
        if ($this->hero) {
            $query->where('products.id', '!=', $this->hero->getKey());
        }

        foreach ($this->filters as $bindKey => $values) {
            $values = array_values(array_filter((array) $values, fn($v) => $v !== '' && $v !== null));
            if (!empty($values)) {
                $query->whereHas('variants.attributes', function ($q) use ($bindKey, $values) {
                    $q->whereIn('attribute_variants.value', $values)
                    ->whereHas('attribute', fn ($aq) => $aq->whereRaw(
                        'REPLACE(LOWER(`key`), " ", "_") = ?',
                        [\Illuminate\Support\Str::slug($bindKey, '_')]
                    ));
                });
            }
        }

        $products = $query->with(['media','collections'])
            ->orderByDesc('products.created_at')
            ->paginate($this->perPage);

        $showHero = $this->hero && $products->currentPage() === 1;
        $totalWithHero = $products->total() + ($this->hero ? 1 : 0);

        return view('livewire.collections-page', compact('products', 'showHero', 'totalWithHero'));
    }
}