<?php

namespace App\Livewire;

use App\Actions\Webshop\AddProductToCart;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Money\Money;

class Product extends Component
{
    use InteractsWithBanner;

    // ─────────────────────────────────────────────────────────────
    // Estado público
    // ─────────────────────────────────────────────────────────────
    public $productId;
    public $variant;
    public ?string $couponCode = null;
    public bool $discountApplied = false;
    public int $discountAmount = 0;
    public int $quantity = 1;

    public $rules = [
        'variant'       => ['nullable', 'exists:App\Models\ProductVariant,id'],
        'couponCode'    => ['nullable', 'string', 'max:32'],
        'quantity'      => ['required', 'integer', 'min:1'],
    ];

    protected $listeners = [
        'couponApplied'     => 'handleCouponApplied',
        'productAddedToCart'=> 'updateStockInfo',
    ];

    protected function messages()
    {
        return [
            'variant.exists' => 'La variante seleccionada no existe o es inválida.',
        ];
    }

    public function mount()
    {
        // Selecciona la primera variante disponible del producto
        $this->variant = $this->product->variants()->value('id');
    }

    // ─────────────────────────────────────────────────────────────
    // Producto (cargado con relaciones).
    // ─────────────────────────────────────────────────────────────
    #[Computed]
    public function product()
    {
        return \App\Models\Product::with([
            'variants.attributes',
            'variants.media',
        ])->findOrFail($this->productId);
    }

    // ─────────────────────────────────────────────────────────────
    // Pricing & Discounts (oferta + cálculo de precios)
    // ─────────────────────────────────────────────────────────────
    #[Computed]
    public function hasDiscount(): bool
    {
        return $this->product->compare_at_price !== null
            && $this->product->compare_at_price->greaterThan($this->product->price);
    }

    // Precio “original” a mostrar tachado (si hay oferta)
    #[Computed]
    public function originalPrice(): ?Money
    {
        // Si hay oferta, compare; si no, price (asume 'price' nunca es null)
        return $this->hasDiscount ? $this->product->compare_at_price : null;
    }

    // Base sobre la que se aplica el cupón (precio vigente)
    #[Computed]
    public function basePrice(): Money
    {
        return $this->product->price;
    }

    // Precio final mostrado (oferta + cupón si aplica)
    #[Computed]
    public function finalPrice(): \Money\Money
    {
        $price = $this->product->price; // Money (no null)

        if ($this->discountApplied && $this->couponPercent()) {
            $off = (int) round($price->getAmount() * ($this->couponPercent() / 100));
            $price = new \Money\Money($price->getAmount() - $off, $price->getCurrency());
        }

        return $price;
    }

    // ─────────────────────────────────────────────────────────────
    // Cupones
    // ─────────────────────────────────────────────────────────────
    protected function couponPercent(): ?int
    {
        if (!$this->couponCode) return null;

        $coupon = Coupon::where('code', $this->couponCode)
            ->whereHas('products', fn ($q) => $q->where('products.id', $this->productId))
            ->valid()
            ->first();

        return $coupon?->percent ?? null; // ajusta al campo real del modelo
    }

    public function applyCoupon()
    {
        $this->validate(['couponCode' => 'required|string']);

        $percent = $this->couponPercent();

        if ($percent) {
            $this->discountApplied = true;
            $this->dispatch('couponApplied', code: $this->couponCode);
        } else {
            $this->reset(['couponCode', 'discountApplied']);
        }
    }

    public function handleCouponApplied($code)
    {
        $this->couponCode = $code;
        $this->discountApplied = (bool) $this->couponPercent();
    }

    // ─────────────────────────────────────────────────────────────
    // Stock / Variantes
    // ─────────────────────────────────────────────────────────────
    #[Computed]
    public function selectedVariant()
    {
        return $this->variant
            ? $this->product->variants->firstWhere('id', $this->variant)
            : null;
    }

    #[Computed]
    public function availableStock(): int
    {
        $cart = Auth::user()?->cart
            ?? Cart::where('session_id', session()->getId())->first();

        if ($this->variant) {
            $variant = $this->selectedVariant();
            if (! $variant) return 0;

            $inCart = $cart?->items()
                ->where('product_id', $this->productId)
                ->where('product_variant_id', $this->variant)
                ->sum('quantity') ?? 0;

            return max(0, (int)$variant->total_variant_stock - (int)$inCart);
        }

        $inCart = $cart?->items()
            ->where('product_id', $this->productId)
            ->whereNull('product_variant_id')
            ->sum('quantity') ?? 0;

        return max(0, (int)$this->product->computed_total_stock - (int)$inCart);
    }

    #[Computed]
    public function maxQuantity(): int
    {
        return $this->availableStock;
    }

    public function updatedVariant()
    {
        $this->quantity = 1;
    }

    // ─────────────────────────────────────────────────────────────
    // Media
    // ─────────────────────────────────────────────────────────────
    #[Computed]
    public function allProductImages()
    {
        $images = [];

        foreach ($this->product->getMedia('images') as $media) {
            $images[] = [
                'original'  => $media->getUrl(),
                'thumbnail' => $media->getUrl('sm_thumb'),
            ];
        }

        foreach ($this->product->variants as $variant) {
            if ($media = $variant->getFirstMedia('product-variant-image')) {
                $images[] = [
                    'original'  => $media->getUrl(),
                    'thumbnail' => $media->getUrl('sm_thumb'),
                ];
            }
        }

        return $images;
    }

    // ─────────────────────────────────────────────────────────────
    // Acciones
    // ─────────────────────────────────────────────────────────────
    public function addToCart(AddProductToCart $cart)
    {
        $this->validate();

        if ($this->quantity > $this->availableStock) {
            $this->addError('quantity', 'No hay suficientes unidades disponibles.');
            return;
        }

        try {
            $cart->add(
                quantity:   $this->quantity,
                productId:  $this->productId,
                variantId:  $this->variant,
                couponCode: $this->discountApplied ? $this->couponCode : null
            );

            $this->dispatch('$refresh')->to(NavigationCart::class);
            $this->banner('Producto agregado al carrito');
            $this->dispatch('productAddedToCart');
        } catch (\Exception $e) {
            $this->addError('variant', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Render
    // ─────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.product');
    }
}
