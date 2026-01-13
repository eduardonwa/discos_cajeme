<?php

namespace App\Livewire;

use Log;
use App\Models\Coupon;
use Livewire\Component;
use App\Factories\CartFactory;
use Masmerise\Toaster\Toaster;
use Masmerise\Toaster\Toastable;
use Livewire\Attributes\Computed;
use Laravel\Jetstream\InteractsWithBanner;
use App\Exceptions\MinimumPurchaseAmountException;
use App\Actions\Webshop\CreateStripeCheckoutSession;

class Cart extends Component
{
    use Toastable, InteractsWithBanner;
    
    public $context;
    public $emptyCart = '';
    public $errorMessage = '';
    public $minimumAmount = '';
    public $showError = false;
    public $targetId;

    public $listeners = [
        'cartUpdated' => '$refresh',
        'couponApplied' => 'applyCoupon',
    ];
    
    #[Computed]
    public function cart()
    {
        return CartFactory::make()->loadMissing([
            'items',
            'items.product',
            'items.variant',
            'items.variant.attributes.attribute',
        ]);
    }

    public function coupon()
    {
        return $this->cart->coupon_code
            ? Coupon::where('code', $this->cart->coupon_code)->valid()->first()
            : null;
    }

    public function applyCoupon($code)
    {
        $coupon = Coupon::where('code', $code)
            ->valid()
            ->first();
    
        if ($coupon) {
            $this->cart->update(['coupon_code' => $code]);
            $this->banner('Cupón aplicado correctamente');
            $this->dispatch('cartUpdated');
        } else {
            $this->addError('coupon', 'Cupón no válido o expirado.');
        }
    }

    #[Computed]
    public function totalWithDiscount()
    {
        $subtotal = $this->cartSubtotal();
    
        if ($coupon = $this->coupon()) {
            // Aplicar descuento según el alcance del cupón
            if ($coupon->scope === 'cart') {
                return $coupon->applyDiscount($subtotal);
            } else {
                // Si el cupón es para productos individuales, aplicar el descuento a cada producto
                $total = 0;
                foreach ($this->cart->items as $item) {
                    // Buscar si el cupón aplica a este producto
                    if ($coupon->products->contains($item->product_id)) {
                        $total += $coupon->applyDiscount($item->product->price->getAmount() * $item->quantity, $subtotal);
                    } else {
                        $total += $item->product->price->getAmount() * $item->quantity;
                    }
                }
                return $total;
            }
        }
    
        return $subtotal;
    }

    #[Computed]
    public function cartSubtotal()
    {
        return $this->cart->items->sum(fn($item) =>
            $item->product->price->getAmount() * $item->quantity
        );
    }

    #[Computed]
    public function discountDetails()
    {
        if (!$coupon = $this->coupon()) return null;

        $subtotal = $this->cart->items->sum(fn($item) =>
            $item->product->price->getAmount() * $item->quantity
        );

        return [
            'code' => $coupon->code,
            'type' => $coupon->discount_type,
            'value' => $coupon->discount_value,
            'amount' => $subtotal,
            'formatted' => $coupon->discount_type === 'percentage'
                ? $coupon->discount_value . '%'
                : '$' . number_format($coupon->discount_value / 100, 2),
            'amount_formatted' => '$' . number_format($subtotal / 100, 2)
        ];
    }

    public function removeCoupon()
    {
        $this->cart->update(['coupon_code' => null]);
        $this->dispatch('cartUpdated');
    }

    #[Computed]
    public function items()
    {
        return $this->cart->items;
    }

    public function increment($itemId)
    {
        $item = $this->cart->items()->find($itemId);
    
        if (!$item) {
            $this->dangerBanner('Error', 'Ítem no encontrado');
            return;
        }
    
        $maxQuantity = $item->variant
            ? $item->variant->total_variant_stock
            : $item->product->total_product_stock;
        
        // Calcular cuántas unidades ya están en el carrito (de este mismo item)
        $inCart = $this->cart->items()
            ->where('product_id', $item->product_id)
            ->when($item->product_variant_id, fn($q) => $q->where('product_variant_id', $item->product_variant_id))
            ->sum('quantity');
        
        $available = $maxQuantity - $inCart;
    
        if ($available < 1) {
            $this->dangerBanner(
                '¡Ups! No hay más unidades'
            );
            return;
        }
    
        $item->increment('quantity');
        $this->dispatch('cartUpdated');
        $this->banner('Cantidad incrementada');
    }
    
    public function decrement($itemId)
    {
        $item = $this->cart->items()->find($itemId);

        if (!$item) {
            return;
        }

        if ($item->quantity > 1) {
            $item->decrement('quantity');
        } else {
            $item->delete();
        }

        $this->dispatch('productRemovedFromCart');
        $this->dispatch('cartUpdated');
    }

    public function delete($itemId)
    {
        $this->cart->items()->where('id', $itemId)->delete();

        $this->dispatch('productRemovedFromCart');
        $this->dispatch('cartUpdated');
    }

    public function checkout(CreateStripeCheckoutSession $checkoutSession)
    {
        try {
            if ($this->cart->total->getAmount() < 1000) { // 10 pesos
                throw new MinimumPurchaseAmountException();
            }
    
            // Si no hay errores, crear la sesión de pago
            return $checkoutSession->createFromCart($this->cart);
        } catch (MinimumPurchaseAmountException $e) {
            // Concatenar el mensaje de la excepción con el enlace HTML
            $message = $e->getMessage() . ' <a href="/ofertas" class="underline">¡Revisa nuestras ofertas!</a>';
    
            // Pasar el mensaje completo al toaster
            Toaster::info($message, [
                'showError' => true,
                'minimumAmount' => $message, // Pasamos el mensaje completo
            ]);
        } catch (\Exception $e) {
            // Otros errores
            Toaster::error('Ocurrió un error al procesar tu solicitud.');
            Log::error('Mensaje de error al procesar la solicitud: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        // si el carrito viene vacío mostrar mensaje
        $this->showError = $this->cart->items->isEmpty();
        $this->emptyCart = $this->showError ? 'Tu carrito está vacío.' : '';

        return view('livewire.cart');
    }
}
