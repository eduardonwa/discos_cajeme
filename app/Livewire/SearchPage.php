<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class SearchPage extends Component
{
    use WithPagination;

    #[Url(history:true)]
    public string $q = '';
    public string $searchQuery = '';

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
            ->limit(13)
            ->get();
    }

    public function updateQ()
    {
        $this->resetPage();
    }
    
    public function render()
    {
        $products = Product::query()
            ->published()
            ->when($this->q, fn ($query) =>
                $query->where('name', 'like', "%{$this->q}%")
            )
            ->with('media')
            ->orderByDesc('created_at')
            ->paginate(24);

        return view('livewire.search-page', compact('products'));
    }
}
