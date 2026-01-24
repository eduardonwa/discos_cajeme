<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class SearchModal extends Component
{
    use WithPagination;
    
    public string $searchQuery = '';
    public bool $searchModal = false;

    #[\Livewire\Attributes\On('open-search')]
    public function open()
    {
        $this->searchModal = true;
    }

    public function updatedSearchQuery()
    {
        $this->resetPage();
    }
    
    public function close()
    {
        $this->reset(['searchQuery', 'searchModal']);
    }

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

    public function render()
    {
        return view('livewire.search-modal');
    }
}
