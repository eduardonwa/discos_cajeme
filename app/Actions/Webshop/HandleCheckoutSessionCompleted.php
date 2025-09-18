<?php

namespace App\Actions\Webshop;

use Exception;
use App\Models\Cart;
use App\Models\User;
use Stripe\LineItem;
use App\Models\Product;
use App\Models\OrderItem;
use App\Events\OrderCreated;
use Laravel\Cashier\Cashier;
use App\Models\ProductVariant;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NewOrderNotification;

class HandleCheckoutSessionCompleted
{
    public function handle($sessionId)
    {
        DB::transaction(function () use ($sessionId) {
            try {
                // Recuperar la sesión de Stripe con los ítems
                $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [
                    'expand' => ['line_items.data.price.product']
                ]);

                \Log::debug('Stripe Session', [
                    'total'     => $session->amount_total / 100,
                    'discount'  => $session->total_details->amount_discount / 100,
                    'metadata'  => $session->metadata->toArray()
                ]);

                $totalTax = 0;
                $subtotal = 0;

                // Obtener el usuario y el carrito de los metadatos
                $user = User::find($session->metadata->user_id);
                $cart = Cart::find($session->metadata->cart_id);

                foreach ($session->line_items->data as $lineItem) {
                    // Verificaciones e impuestos
                    // 1. Que el producto no sea nulo
                    $product = $lineItem->price->product ?? null;
                    if (!$product) {
                        continue; // Ignorar ítems sin producto
                    }
                    // 2. Que amount_total no sea nulo
                    if ($lineItem->amount_total === null) {
                        continue; // Ignorar ítems sin amount_total
                    }
                    // Agregar impuestos
                    $isTax = $product->metadata->is_tax ?? false;
                    if ($isTax) {
                        // Sumar el IVA al total
                        $totalTax += $lineItem->amount_total;
                        continue; // Continuar con el siguiente ítem
                    }

                    // Sumar al subtotal (solo productos)
                    $subtotal += $lineItem->amount_total;
                    $quantity = $lineItem->quantity;

                    // Manejar producto con o sin variantes
                    $productId = $product->metadata->product_id ?? null;
                    $variantId = $product->metadata->product_variant_id ?? null;

                    if ($variantId) {
                        $variant = ProductVariant::findOrFail($variantId);
                        $variant->decreaseStock($quantity);
                    } elseif ($productId) {
                        $product = Product::findOrFail($productId);
                        $product->decreaseStock($quantity);
                    }
                }

                // Direcciones para el envío
                $order = $user->orders()->create([
                    'stripe_checkout_session_id'    => $session->id,
                    'amount_shipping'               => $session->total_details->amount_shipping,
                    'amount_discount'               => $session->total_details->amount_discount,
                    'amount_tax'                    => $totalTax,
                    'amount_subtotal'               => $subtotal,
                    'amount_total'                  => $subtotal + $totalTax,
                    'billing_address' => [
                        'name'          => $session->customer_details->name,
                        'city'          => $session->customer_details->address->city,
                        'country'       => $session->customer_details->address->country,
                        'line1'         => $session->customer_details->address->line1,
                        'line2'         => $session->customer_details->address->line2,
                        'postal_code'   => $session->customer_details->address->postal_code,
                        'state'         => $session->customer_details->address->state,
                    ],
                    'shipping_address' => [
                        'name'          => $session->shipping_details->name,
                        'city'          => $session->shipping_details->address->city,
                        'country'       => $session->shipping_details->address->country,
                        'line1'         => $session->shipping_details->address->line1,
                        'line2'         => $session->shipping_details->address->line2,
                        'postal_code'   => $session->shipping_details->address->postal_code,
                        'state'         => $session->shipping_details->address->state,
                    ]
                ]);

                // Crear una nueva orden con los productos
                $lineItems = Cashier::stripe()->checkout->sessions->allLineItems($session->id);
                // \Log::debug('LineItems obtenidos de Stripe', ['count' => count($lineItems->all())]);

                $lineItems = Cashier::stripe()->checkout->sessions->allLineItems($session->id);
                // \Log::debug('LineItems obtenidos de Stripe', ['count' => count($lineItems->all())]);
                
                $orderItems = collect($lineItems->all())->map(function (LineItem $line) {
                    try {
                        \Log::debug('Procesando LineItem', [
                            'line_id' => $line->id,
                            'price_id' => $line->price->id,
                            'amount_total' => $line->amount_total,
                            'quantity' => $line->quantity
                        ]);
                
                        $product = Cashier::stripe()->products->retrieve($line->price->product);
                        \Log::debug('Producto de Stripe', [
                            'product_id' => $product->id,
                            'name' => $product->name,
                            'metadata' => $product->metadata
                        ]);
                
                        // Validación de campos monetarios
                        $unitAmount = $line->price->unit_amount ?? 0;
                        $amountTotal = $line->amount_total ?? ($unitAmount * $line->quantity);
                        $amountDiscount = $line->amount_discount ?? 0;
                        $amountSubtotal = $line->amount_subtotal ?? ($unitAmount * $line->quantity);
                        $amountTax = $line->amount_tax ?? 0;
                
                        \Log::debug('Valores monetarios validados', [
                            'unit_amount' => $unitAmount,
                            'amount_total' => $amountTotal,
                            'amount_discount' => $amountDiscount,
                            'amount_subtotal' => $amountSubtotal,
                            'amount_tax' => $amountTax
                        ]);
                
                        // Obtener IDs de producto/variante
                        $productId = $product->metadata->product_id ?? null;
                        $variantId = $product->metadata->product_variant_id ?? null;
                        \Log::debug('IDs obtenidos', [
                            'product_id' => $productId,
                            'variant_id' => $variantId
                        ]);
                
                        // Procesar descripción
                        $description = 'Ejemplar único';
                        if ($variantId) {
                            $variant = ProductVariant::with('attributes')->find($variantId);
                            if ($variant) {
                                $description = $variant->attributes->map(function ($av) {
                                    return "{$av->attribute->key}: {$av->value}";
                                })->implode(' / ');
                                \Log::debug('Atributos de variante', ['description' => $description]);
                            } else {
                                \Log::warning('Variante no encontrada', ['variant_id' => $variantId]);
                            }
                        }
                
                        $orderItemData = [
                            'product_id'            => $variantId ? null : $productId,
                            'product_variant_id'    => $variantId,
                            'name'                  => $product->name,
                            'description'           => $description,
                            'price'                 => $unitAmount,
                            'quantity'              => $line->quantity,
                            'amount_discount'       => $amountDiscount,
                            'amount_subtotal'       => $amountSubtotal,
                            'amount_tax'            => $amountTax,
                            'amount_total'          => $amountTotal,
                        ];
                
                        \Log::debug('Creando OrderItem con datos completos', $orderItemData);
                        return new OrderItem($orderItemData);
                
                    } catch (\Exception $e) {
                        \Log::error('Error al procesar LineItem', [
                            'error' => $e->getMessage(),
                            'line_item' => $line->id ?? null,
                            'trace' => $e->getTraceAsString()
                        ]);
                        return null;
                    }
                })->filter();
                
                \Log::info('OrderItems creados exitosamente', ['count' => $orderItems->count()]);

                // Guardar los ítems de la orden
                $order->items()->saveMany($orderItems);

                // Eliminar el carrito
                $cart->items()->delete();
                $cart->delete();

                Mail::to($user)->send(new OrderConfirmation($order));
                $user->notify(new NewOrderNotification($order));
            } catch (\Exception $e) {
                // Relanzar la excepción para que la transacción se revierta
                throw $e;
            }
        });
    }
}