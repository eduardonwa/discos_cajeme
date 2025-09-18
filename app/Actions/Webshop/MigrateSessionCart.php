<?php

namespace App\Actions\Webshop;

use App\Models\Cart;

class MigrateSessionCart
{
    public function migrate(Cart $sessionCart, Cart $userCart)
    {
        $sessionCart->items->each(fn($item) => (new AddProductToCart())->add(
            cart:       $userCart,
            productId:  $item->product_id,
            variantId:  $item->product_variant_id,
            quantity:   $item->quantity,
        ));

        $sessionCart->items()->delete();
        $sessionCart->delete();
    }
}