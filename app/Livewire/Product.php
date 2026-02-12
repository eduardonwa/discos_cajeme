<?php

namespace App\Livewire;

use Money\Money;
use App\Models\Cart;
use App\Models\Coupon;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Models\Product as ProductModel;
use App\Actions\Webshop\AddProductToCart;
use App\Models\ProductVariant;
use Laravel\Jetstream\InteractsWithBanner;

class Product extends Component
{
    use InteractsWithBanner;

    // ─────────────────────────────────────────────────────────────
    // Estado público
    // ─────────────────────────────────────────────────────────────
    public ProductModel $product;
    public ?int $variant = null;
    public ?string $couponCode = null;
    public bool $discountApplied = false;
    public int $discountAmount = 0;
    public int $quantity = 1;

    public $rules = [
        'variant'       => ['nullable', 'integer', 'exists:App\Models\ProductVariant,id'],
        'couponCode'    => ['nullable', 'string', 'max:32'],
        'quantity'      => ['required', 'integer', 'min:1'],
    ];

    protected $listeners = [
        'couponApplied'     => 'handleCouponApplied',
        'productAddedToCart'=> 'updateStockInfo'
    ];

    protected function messages()
    {
        return [
            'variant.exists' => 'La variante seleccionada no existe o es inválida.',
        ];
    }

    public function mount(ProductModel $product)
    {
        $this->product = $product->load([
            'variants.attributes',
            'variants.media'
        ]);

        $default = $this->product->variants
            ->where('is_active', true)
            ->sortByDesc('is_default')
            ->first();

        $this->variant = $default?->id;
    }

    // ─────────────────────────────────────────────────────────────
    // Pricing & Discounts (oferta + cálculo de precios)
    // ─────────────────────────────────────────────────────────────
    #[Computed]
    public function hasDiscount(): bool
    {
        return (bool) ($this->selectedVariant()?->has_discount);
    }

    #[Computed]
    public function originalPrice(): ?Money
    {
        return $this->selectedVariant()?->original_price;
    }

    // Base sobre la que se aplica el cupón (precio vigente)
    #[Computed]
    public function basePrice(): Money
    {
        return $this->selectedVariant()?->final_price
            ?? new Money(0, new \Money\Currency('MXN'));
    }

    // Precio final mostrado (oferta + cupón si aplica)
    #[Computed]
    public function finalPrice(): \Money\Money
    {
        $price = $this->basePrice(); // Money

        $percent = $this->discountApplied ? $this->couponPercent() : null;
        if ($percent) {
            $off = (int) round($price->getAmount() * ($percent / 100));
            return new Money($price->getAmount() - $off, $price->getCurrency());
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
            ->whereHas('products', fn ($q) => $q->where('products.id', $this->product->id))
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
    public function selectedVariant(): ?ProductVariant
    {
        return $this->variant
            ? $this->product->variants->firstWhere('id', $this->variant)
            : null;
    }

    #[Computed]
    public function availableStock(): int
    {
        $variant = $this->selectedVariant();
        if (! $variant) return 0;

        $cart = Auth::user()?->cart
            ?? Cart::where('session_id', session()->getId())->first();

        $inCart = (int) ($cart?->items()
            ->where('product_variant_id', $variant->id)
            ->sum('quantity') ?? 0);

        return max(0, (int) $variant->total_variant_stock - $inCart);
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

    public function updateStockInfo(): void
    {
        $this->dispatch('$refresh');
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
                productId:  $this->product->id,
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

    public function render()
    {
        return view('livewire.product');
    }
}
