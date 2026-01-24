<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class SearchPage extends Component
{
    use WithPagination;

    #[Url(history:true)]
    public string $q = '';

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
