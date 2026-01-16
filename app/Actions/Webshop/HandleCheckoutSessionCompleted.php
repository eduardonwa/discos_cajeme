<?php

namespace App\Actions\Webshop;

use App\Models\Cart;
use App\Models\User;
use Stripe\LineItem;
use App\Models\OrderItem;
use Laravel\Cashier\Cashier;
use App\Models\ProductVariant;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NewOrderNotification;

class HandleCheckoutSessionCompleted
{
    /**
     * Mapa mental (flujo):
     * 1) Traer Checkout Session + line_items (expand) para leer metadata por item
     * 2) Resolver user/cart desde metadata (y fallar temprano si faltan)
     * 3) Recorrer line_items expandido:
     *    - si es IVA -> acumular tax
     *    - si es producto -> sumar subtotal y descontar stock por ProductVariant
     * 4) Crear Order (con totales + direcciones)
     * 5) Volver a traer line items (allLineItems) y construir OrderItems (skip IVA)
     * 6) Guardar items, borrar carrito, notificar
     */
    public function handle(string $sessionId): void
    {
        $trace = "checkout_completed:{$sessionId}";
        \Log::info("$trace START");

        DB::transaction(function () use ($sessionId, $trace) {
            try {
                // 1) Recuperar sesión con items expandidos (para metadata por item)
                $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [
                    'expand' => ['line_items.data.price.product'],
                ]);

                \Log::info("$trace SESSION", [
                    'session_id' => $session->id ?? null,
                    'mode' => $session->mode ?? null,
                    'payment_status' => $session->payment_status ?? null,
                    'customer_email' => $session->customer_details->email ?? null,
                    'line_items_count' => $session->line_items->total_count ?? null,
                    'metadata' => method_exists($session->metadata, 'toArray')
                        ? $session->metadata->toArray()
                        : (array) $session->metadata,
                ]);

                $totalTax = 0;
                $subtotal = 0;

                // 2) Resolver user/cart desde metadata (falla temprano si falta algo)
                $userId = $session->metadata->user_id ?? null;
                $cartId = $session->metadata->cart_id ?? null;

                \Log::info("$trace META IDS", [
                    'user_id' => $userId,
                    'cart_id' => $cartId,
                    'coupon_code' => $session->metadata->coupon_code ?? null,
                ]);

                $user = $userId ? User::find($userId) : null;
                $cart = $cartId ? Cart::find($cartId) : null;

                throw_if(!$user, new \RuntimeException('User no encontrado desde metadata'));
                throw_if(!$cart, new \RuntimeException('Cart no encontrado desde metadata'));

                // 3) Descontar stock por SKU (ProductVariant) usando metadata de Stripe Product
                foreach ($session->line_items->data as $lineItem) {
                    $stripeProduct = $lineItem->price->product ?? null; // por expand viene como objeto

                    \Log::debug("$trace LINE_ITEM", [
                        'line_item_id' => $lineItem->id ?? null,
                        'quantity' => $lineItem->quantity ?? null,
                        'amount_total' => $lineItem->amount_total ?? null,
                        'price_id' => $lineItem->price->id ?? null,
                        'stripe_product_id' => is_object($stripeProduct) ? ($stripeProduct->id ?? null) : $stripeProduct,
                    ]);

                    // Ignorar items incompletos
                    if (!$stripeProduct || $lineItem->amount_total === null) {
                        \Log::warning("$trace LINE_ITEM SKIPPED (missing product/amount_total)", [
                            'line_item_id' => $lineItem->id ?? null,
                        ]);
                        continue;
                    }

                    // IVA (lo metiste como line item con metadata is_tax=true)
                    $isTax = $stripeProduct->metadata->is_tax ?? false;
                    if ($isTax) {
                        $totalTax += $lineItem->amount_total;
                        continue;
                    }

                    // Subtotal de productos (ya incluye descuento prorrateado si lo aplicaste en unit_amount)
                    $subtotal += $lineItem->amount_total;

                    $qty = (int) ($lineItem->quantity ?? 1);
                    $variantId = $stripeProduct->metadata->product_variant_id ?? null;

                    throw_if(
                        !$variantId,
                        new \RuntimeException('Line item sin product_variant_id (SKU vendible requerido)')
                    );

                    \Log::info("$trace STOCK DEBIT", [
                        'variant_id' => $variantId,
                        'qty' => $qty,
                        'stripe_product_id' => $stripeProduct->id ?? null,
                    ]);

                    $variant = ProductVariant::findOrFail($variantId);
                    $variant->decreaseStock($qty);
                }

                \Log::info("$trace TOTALS", [
                    'subtotal' => $subtotal,
                    'tax' => $totalTax,
                    'grand_total' => $subtotal + $totalTax,
                ]);

                // 4) Crear orden (direcciones + totales)
                $order = $user->orders()->create([
                    'stripe_checkout_session_id' => $session->id,
                    'amount_shipping'            => $session->total_details->amount_shipping,
                    'amount_discount'            => $session->total_details->amount_discount,
                    'amount_tax'                 => $totalTax,
                    'amount_subtotal'            => $subtotal,
                    'amount_total'               => $subtotal + $totalTax,

                    'billing_address' => [
                        'name'        => $session->customer_details->name ?? null,
                        'city'        => $session->customer_details->address->city ?? null,
                        'country'     => $session->customer_details->address->country ?? null,
                        'line1'       => $session->customer_details->address->line1 ?? null,
                        'line2'       => $session->customer_details->address->line2 ?? null,
                        'postal_code' => $session->customer_details->address->postal_code ?? null,
                        'state'       => $session->customer_details->address->state ?? null,
                    ],

                    'shipping_address' => [
                        'name'         => $session->shipping_details->name ?? null,
                        'city'         => $session->shipping_details->address->city ?? null,
                        'country'      => $session->shipping_details->address->country ?? null,
                        'line1'        => $session->shipping_details->address->line1 ?? null,
                        'line2'        => $session->shipping_details->address->line2 ?? null,
                        'postal_code'  => $session->shipping_details->address->postal_code ?? null,
                        'state'        => $session->shipping_details->address->state ?? null,
                    ],
                ]);

                \Log::info("$trace ORDER CREATED", [
                    'order_id' => $order->id ?? null,
                    'stripe_checkout_session_id' => $session->id ?? null,
                ]);

                // 5) Construir OrderItems (usamos allLineItems y consultamos Stripe Product para metadata)
                $lineItems = Cashier::stripe()->checkout->sessions->allLineItems($session->id);
                \Log::info("$trace ALL_LINE_ITEMS", ['count' => count($lineItems->all())]);

                $orderItems = collect($lineItems->all())
                    ->map(function (LineItem $line) use ($trace) {
                        try {
                            $stripeProductId = $line->price->product ?? null;
                            throw_if(!$stripeProductId, new \RuntimeException('LineItem sin price.product'));

                            // Recuperar Stripe Product para leer metadata (variantId, is_tax)
                            $stripeProduct = Cashier::stripe()->products->retrieve($stripeProductId);

                            // Skip IVA
                            if (($stripeProduct->metadata->is_tax ?? false) === true) {
                                return null;
                            }

                            $variantId = $stripeProduct->metadata->product_variant_id ?? null;
                            throw_if(!$variantId, new \RuntimeException('OrderItem sin product_variant_id'));

                            $variant = ProductVariant::with(['product', 'attributes.attribute'])->findOrFail($variantId);

                            $description = $variant->attributes->isNotEmpty()
                                ? $variant->attributes->map(fn($av) => "{$av->attribute->key}: {$av->value}")->implode(' / ')
                                : 'Variante estándar';

                            // Valores monetarios (Stripe)
                            $unitAmount     = $line->price->unit_amount ?? 0;
                            $qty            = (int) ($line->quantity ?? 1);
                            $amountTotal    = $line->amount_total ?? ($unitAmount * $qty);
                            $amountDiscount = $line->amount_discount ?? 0;
                            $amountSubtotal = $line->amount_subtotal ?? ($unitAmount * $qty);
                            $amountTax      = $line->amount_tax ?? 0;

                            \Log::debug("$trace ORDERITEM BUILD", [
                                'stripe_product_id' => $stripeProduct->id ?? null,
                                'variant_id' => $variantId,
                                'qty' => $qty,
                                'unit_amount' => $unitAmount,
                                'amount_total' => $amountTotal,
                            ]);

                            return new OrderItem([
                                'product_id'         => $variant->product_id,   // opcional (cache)
                                'product_variant_id' => $variant->id,           // ✅ fuente de verdad
                                'name'               => $stripeProduct->name ?? $variant->product->name,
                                'description'        => $description,
                                'price'              => $unitAmount,
                                'quantity'           => $qty,
                                'amount_discount'    => $amountDiscount,
                                'amount_subtotal'    => $amountSubtotal,
                                'amount_tax'         => $amountTax,
                                'amount_total'       => $amountTotal,
                            ]);
                        } catch (\Exception $e) {
                            \Log::error("$trace ORDERITEM FAILED", [
                                'error' => $e->getMessage(),
                                'line_item_id' => $line->id ?? null,
                                'stripe_price_id' => $line->price->id ?? null,
                            ]);
                            return null;
                        }
                    })
                    ->filter()
                    ->values();

                \Log::info("$trace ORDERITEMS READY", ['count' => $orderItems->count()]);

                // Guardar ítems de la orden
                $order->items()->saveMany($orderItems);

                // 6) Limpiar carrito y notificar
                $cart->items()->delete();
                $cart->delete();

                Mail::to($user)->send(new OrderConfirmation($order));
                $user->notify(new NewOrderNotification($order));

                \Log::info("$trace DONE", [
                    'order_id' => $order->id ?? null,
                    'items_count' => $orderItems->count(),
                ]);
            } catch (\Exception $e) {
                \Log::error("$trace FAILED", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    // Ojo: es largo, pero útil si estás debuggeando
                    'trace' => $e->getTraceAsString(),
                ]);

                // relanzar para rollback de transacción
                throw $e;
            }
        });
    }
}
