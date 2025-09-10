<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Collection;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class StoreFront extends Component
{
    use WithPagination;

    #[Url]
    public $searchQuery = '';

    #[Computed]
    public function productsQuery()
    {
        return Product::query()
            ->published()
            ->when($this->searchQuery, fn($query) =>
                $query->where('name', 'like', "%{$this->searchQuery}%")
            )
            ->with('media')
            ->orderByDesc('created_at')
            ->paginate(12);
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }

    private function getCollectionWithProducts(string $slug, int $limit)
    {
        return Collection::query()
            ->active()                              // tu scope
            ->where('slug', $slug)
            ->with(['products' => function ($q) use ($limit) {
                $q->where('published', true)
                  ->with(['media','collections'])   // Spatie + chips sin N+1
                  ->orderByDesc('products.created_at')
                  ->limit($limit);
            }])
            ->first(); // devuelve Collection|null
    }

    public function render()
    {
        // Cada colección por separado (sueltas)
        $verano     = $this->getCollectionWithProducts('verano-libre-40', 3);
        $onSale     = $this->getCollectionWithProducts('on-sale', 3);
        $atemporal  = $this->getCollectionWithProducts('atemporal', 6);
        $comodidad  = $this->getCollectionWithProducts('comodidad-moderna', 6);

        return view('livewire.store-front', compact('verano','onSale','atemporal','comodidad'));
    }
}
