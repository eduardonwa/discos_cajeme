<div class="coupon">
    <form wire:submit.prevent="applyCoupon">
        <label class="ff-semibold clr-neutral-700">
            Código de descuento
        </label>
        <div class="coupon__zone">
            <input
                type="text"
                data-type="coupon"
                wire:model="couponCode"
                placeholder="@if($context === 'product') Cupón para este producto @else Cupón para el carrito @endif"
            >
            <button
                type="submit"
                class="button ff-semibold"
                data-type="coupon"
            >
                Aplicar
            </button>
        </div>
        @error('couponCode')
            <p class="text-error">{{ $message }}</p>
        @enderror
    </form>
</div>