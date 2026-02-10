<?php

namespace App\Actions\Webshop;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class AddProductToCart
{
    public function add(int $productId, ?int $variantId = null, int $quantity = 1, $cart = null, ?string $couponCode = null)
    {
        $product = Product::with('variants')->findOrFail($productId);

        $variant = $variantId
            ? ProductVariant::where('product_id', $product->id)->whereKey($variantId)->firstOrFail()
            : $product->variants()
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->first();

        throw_if(! $variant, new \RuntimeException('Producto sin variante vendible.'));
        
        $this->validateStock($variant, $quantity, $cart);

        $cart = $cart ?: $this->getOrCreateCart();

        $this->addOrUpdateCartItem($cart, $variant, $quantity);

        if ($couponCode) {
            $cart->update(['coupon_code' => $couponCode]);
        }
    }

    protected function getOrCreateCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }
        
        return Cart::firstOrCreate(['session_id' => session()->getId()]);
    }

    protected function validateStock(ProductVariant $variant, int $quantity, ?Cart $cart = null): void
    {
        $cart = $cart ?: $this->getOrCreateCart();

        $inCart = (int) $cart->items()
            ->where('product_variant_id', $variant->id)
            ->sum('quantity');

        $desired = $inCart + $quantity;
        throw_unless(
            $variant->total_variant_stock >= $desired,
            new \Exception(
                'No hay suficiente stock. Disponibles: ' .
                max($variant->total_variant_stock - $inCart, 0)
            )
        );
    }

    protected function addOrUpdateCartItem(Cart $cart, ?ProductVariant $variant, int $quantity): void
    {
        $item = $cart->items()->where('product_variant_id', $variant->id)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            return;
        }

        $cart->items()->create([
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity
        ]);
    }
}