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
            ? ProductVariant::where('product_id', $product->id)->findOrFail($variantId)
            : $product->variants()
                ->where('is_active', true)
                ->orderByDesc('total_variant_stock')
                ->first();
            
            throw_if(!$variant, new \RuntimeException('Producto sin variante vendible.'));
        
        $this->validateStock($product, $variant, $quantity, $cart);

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

    protected function validateStock(Product $product, ?ProductVariant $variant, int $quantity, ?Cart $cart = null): void
    {
        $cart = $cart ?: $this->getOrCreateCart();
        
        if ($variant) {
            $inCart = $cart->items()
                ->where('product_variant_id', $variant->id)
                ->sum('quantity');
            
            $available = $variant->total_variant_stock - $inCart;

            throw_unless(
                $available >= $quantity,
                new \Exception("No hay suficiente stock. Disponibles: {$available}")
            );
        } else {
            $inCart = $cart->items()
                ->where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->sum('quantity');

            $available = $product->total_product_stock - $inCart;

            throw_unless(
                $available >= $quantity,
                new \Exception("No hay suficiente stock. Disponibles: {$available}")
            );
        }
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