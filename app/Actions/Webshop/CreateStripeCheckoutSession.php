<?php

namespace App\Actions\Webshop;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class CreateStripeCheckoutSession
{
    public function createFromCart(Cart $cart)
    {
        $coupon = $cart->coupon_code 
            ? Coupon::where('code', $cart->coupon_code)->first()
            : null;

        return $cart->user
            ->allowPromotionCodes()
            ->checkout(
                $this->formatCartItems($cart->items, $coupon), [
                    'automatic_tax' => ['enabled' => false], // desactivar Stripe Tax
                    'customer_update' => [
                        'shipping' => 'auto',
                    ],
                    // activar/desactivar esta opción si la tienda realiza envios a otro pais
                    'shipping_address_collection' => [
                        'allowed_countries' => ['US', 'MX']
                    ],
                    'success_url' => route('checkout-status') . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('cart'),
                    'metadata' => [
                        'user_id' => $cart->user->id,
                        'cart_id' => $cart->id,
                        'coupon_code' => $coupon?->code,
                        'discount_type' => $coupon?->discount_type,
                        'discount_value' => $coupon?->discount_value,
                        // 'discount_amount' => $totalDiscount ?? 0
                    ],
                ]
        );
    }

    private function formatCartItems(Collection $items, ?Coupon $coupon)
    {
        $taxRate = 0.16; // IVA del 16%

        $items->loadMissing('variant.product', 'variant.attributes.attribute');
        
        // 1. Calcular subtotal SIN descuento
        $subtotalWithoutDiscount = $items->sum(fn($item) => 
            $item->variant->product->price->getAmount() * $item->quantity
        );
        
        // 2. Aplicar descuento GLOBAL si existe cupón válido
        $discountedTotal = $coupon && $coupon->isValid() 
            ? $coupon->applyDiscount($subtotalWithoutDiscount, $subtotalWithoutDiscount)
            : $subtotalWithoutDiscount;
        
        $totalDiscount = $subtotalWithoutDiscount - $discountedTotal;
        $discountRatio = $subtotalWithoutDiscount > 0 
            ? $discountedTotal / $subtotalWithoutDiscount 
            : 1;

        // 3. Preparar items con descuento proporcional
        $formattedItems = $items->map(function (CartItem $item) use ($discountRatio, $coupon) {
            $variant = $item->variant;
            throw_if(!$variant, new RuntimeException('CartItem sin variante'));

            $product = $variant->product;
            $basePrice = $product->price->getAmount();
            $discountedPrice = (int) round($basePrice * $discountRatio);
            
            $desc = $variant->attributes->isNotEmpty()
                ? $variant->attributes->map(fn($av) => "{$av->attribute->key}: {$av->value}")->implode(' / ')
                : 'Variante estándar';

            return [
                'price_data' => [
                    'currency' => 'MXN',
                    'unit_amount' => $discountedPrice,
                    'product_data' => [
                        'name' => $product->name,
                        'description' => $desc,
                        'metadata' => [
                            'product_variant_id' => $variant->id,
                            'product_id' => $product->id,
                            'original_price' => $basePrice,
                            'discounted_price' => $discountedPrice,
                            'discount_per_unit' => $basePrice - $discountedPrice,
                            'coupon_code' => $coupon?->code
                        ]
                    ]
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // 4. Calcular IVA sobre el total CON descuento
        $totalTax = (int) round($discountedTotal * $taxRate);
        
        if ($totalTax > 0) {
            $formattedItems[] = [
                'price_data' => [
                    'currency' => 'MXN',
                    'unit_amount' => $totalTax,
                    'product_data' => [
                        'name' => 'IVA (16%)',
                        'description' => 'Impuesto al Valor Agregado',
                        'metadata' => ['is_tax' => true],
                    ],
                ],
                'quantity' => 1,
            ];
        }

        \Log::info('Resumen de pago', [
            'subtotal_sin_descuento' => $subtotalWithoutDiscount / 100,
            'descuento_total' => $totalDiscount / 100,
            'subtotal_con_descuento' => $discountedTotal / 100,
            'iva' => $totalTax / 100,
            'total' => ($discountedTotal + $totalTax) / 100
        ]);

        return $formattedItems;
    }
}