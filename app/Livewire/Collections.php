<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Collection;
use Livewire\WithPagination;

class Collections extends Component
{
    use WithPagination;

    public Collection $collection;
    public string $search = '';
    public int $perPage = 12;
    
    public function mount(Collection $collection)
    {
        abort_unless($collection->is_active, 404);
        $this->collection = $collection;
    }

    public function updatingSearch() { $this->resetPage(); }

    public function render()
    {
        $products = $this->collection->products()
            ->published()
            ->when($this->search, fn($q) => 
                $q->where('products.name', 'like', '%'.$this->search.'%')
            )
            ->with(['media', 'collections'])
            ->orderByDesc('products.created_at')
            ->paginate($this->perPage);

        return view('livewire.collections-page', compact('products'));
    }
}