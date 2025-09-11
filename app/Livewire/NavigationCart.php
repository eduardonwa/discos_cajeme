<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class NavigationCart extends Component
{
    public int $bump = 0; // fuerza morphdom

    #[On('productAddedToCart')]
    #[On('productRemovedFromCart')]
    #[On('cartUpdated')]
    public function refreshBadge(): void
    {
        $this->bump++; // garantiza re-render
    }

    #[Computed]
    public function count(): int
    {
        $cart = \App\Factories\CartFactory::make()->fresh(['items']);
        // Si manejas modelo CartItem, preferible:
        // return \App\Models\CartItem::where('cart_id', $cart->id)->sum('quantity');

        return (int) $cart->items()->sum('quantity');
    }

    public function render()
    {
        return view('livewire.navigation-cart');
    }
}
