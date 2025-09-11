<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Coupon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Actions\Webshop\AddProductToCart;
use Laravel\Jetstream\InteractsWithBanner;

class Product extends Component
{
    use InteractsWithBanner;

    public $productId;
    public $variant;
    public $couponCode;
    public $discountApplied = false;
    public $originalPrice;
    public $finalPrice;
    public $discountAmount = 0;
  
    public $rules = [
        'variant' => ['nullable', 'exists:App\Models\ProductVariant,id'],
        'couponCode' => ['nullable', 'string', 'max:32'],
    ];

    protected function messages() 
    {
        return [
            'variant.exists' => 'La variante seleccionada no existe o es inválida.',
        ];
    }

    protected $listeners = [
        'couponApplied' => 'handleCouponApplied',
        'productAddedToCart' => 'updateStockInfo'
    ];

    public function mount()
    {
        // obtiene el ID de la primera variante del producto
        $this->variant = $this->product->variants()->value('id');
        $this->originalPrice = $this->product->price->getAmount();
        $this->finalPrice = $this->originalPrice;
    }

    public function applyCoupon()
    {
        $this->validate(['couponCode' => 'required|string']);

        $coupon = Coupon::where('code', $this->couponCode)
            ->whereHas('products', fn($q) => $q->where('id', $this->productId))
            ->valid()
            ->first();

        if ($coupon) {
            $this->finalPrice = $coupon->applyDiscount($this->originalPrice);
            $this->discountApplied = true;
            $this->dispatch('couponApplied', code: $this->couponCode);
        } else {
            $this->reset(['couponCode', 'discountApplied']);
            $this->finalPrice = $this->originalPrice;
        }
    }

    public function handleCouponApplied($code)
    {
        $this->couponCode = $code;
        $this->discountApplied = true;

        $coupon = Coupon::where('code', $this->couponCode)
            ->whereHas('products', fn($q) => $q->where('products.id', $this->productId))
            ->valid()
            ->first();

        if ($coupon) {
            $this->finalPrice = $coupon->applyDiscount($this->originalPrice);
        }
    }

    public function addToCart(AddProductToCart $cart)
    {
        $this->validate();
        
        try {
            $cart->add(
                productId: $this->productId,
                variantId: $this->variant,
                quantity: 1,
                couponCode: $this->discountApplied ? $this->couponCode : null
            );

            $this->dispatch('$refresh')->to(NavigationCart::class);
            $this->banner('Producto agregado al carrito');
            $this->dispatch('productAddedToCart'); // Disparar evento de actualización
            
        } catch (\Exception $e) {
            $this->addError('variant', $e->getMessage());
        }
    }

    #[Computed]
    public function selectedVariant()
    {
        return $this->variant
            ? $this->product->variants->firstWhere('id', $this->variant)
            : null;
    }

    #[Computed]
    public function availableStock()
    {
        $cart = Auth::user()?->cart ?? Cart::where('session_id', session()->getId())->first();
        
        if ($this->variant) {
            $variant = $this->selectedVariant();
            if (!$variant) return 0;
            
            $inCart = $cart ? $cart->items()
                ->where('product_variant_id', $this->variant)
                ->sum('quantity') : 0;
                
            return max(0, $variant->total_variant_stock - $inCart);
        }
        
        $inCart = $cart ? $cart->items()
            ->where('product_id', $this->productId)
            ->whereNull('product_variant_id')
            ->sum('quantity') : 0;
            
        return max(0, $this->product->total_product_stock - $inCart);
    }

    #[Computed]
    public function maxQuantity()
    {
        return $this->selectedVariant
            ? $this->selectedVariant->total_variant_stock
            : $this->product->total_product_stock;
    }

    #[Computed]
    public function product()
    {
        return \App\Models\Product::with([
            'variants.attributes',
            'variants.media',
        ])->findOrFail($this->productId);
    }

    #[Computed] 
    public function allProductImages()
    {
        $images = [];
        
        // 1. colección 'images'
        foreach ($this->product->getMedia('images') as $media) {
            $images[] = [
                'original' => $media->getUrl(),
                'thumbnail' => $media->getUrl('sm_thumb')
            ];
        }
        
        // 2. colección 'product-variant-image'
        foreach ($this->product->variants as $variant) {
            if ($media = $variant->getFirstMedia('product-variant-image')) {
                $images[] = [
                    'original' => $media->getUrl(),
                    'thumbnail' => $media->getUrl('sm_thumb')
                ];
            }
        }
        
        return $images;
    }

    public function render()
    {
        return view('livewire.product');
    }
}
