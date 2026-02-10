<?php

namespace App\Models;

use Money\Money;
use App\Casts\MoneyCast;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /* ─────────────────────────────────────────
     | Casts & atributos
     ───────────────────────────────────────── */
    protected $casts = [
        'amount_tax'            => MoneyCast::class,
        'amount_total'          => MoneyCast::class,
        'amount_subtotal'       => MoneyCast::class,
        'amount_discount'       => MoneyCast::class,
        'is_admin'              => 'boolean',
        'stock_status'          => 'string',
        'low_stock_threshold'   => 'integer',
    ];

    protected static function booted()
    {
        static::created(function (Product $product) {
            if (app()->runningInConsole()) { return; }
            if ($product->variants()->exists()) { return; }

            $product->variants()->create([
                'title' => 'Default title',
                'price' => 0,
                'total_variant_stock' => 0,
                'compare_at_price' => null,
                'is_default' => true,
                'is_active' => false,
            ]);

            // cache/status del producto
            $product->recalculateStockCache();
        });
    }

    /* ─────────────────────────────────────────
     | Relaciones
     ───────────────────────────────────────── */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function image(): HasOne
    {
        return $this->hasOne(Image::class)->ofMany('featured', 'max');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_product');
    }

    public function coupons()
    {
        return $this->morphToMany(Coupon::class, 'couponable');
    }

    /* ─────────────────────────────────────────
     | Scopes
     ───────────────────────────────────────── */
    public function scopePublished($q)
    {
        return $q->where('published', true);
    }

    public function scopeWithVariantStockSum($q)
    {
        return $q->withSum('variants as variants_stock_sum', 'total_variant_stock');
    }

    /* ─────────────────────────────────────────
     | Inventory (helpers/attrs)
     ───────────────────────────────────────── */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function sumActiveVariantStock(): int
    {
        return (int) $this->variants()
            ->where('is_active', true)
            ->sum('total_variant_stock');
    }

    // suma por variantes activas
    public function getComputedTotalStockAttribute(): int
    {
        return $this->sumActiveVariantStock();
    }

    // ¿Puede abastecer X unidades? (sin considerar carrito)
    public function canFulfill(int $quantity): bool
    {
        return $this->computed_total_stock >= $quantity;
    }

    public function isAvailable(): bool
    {
        return $this->computed_total_stock > 0;
    }

    public function recalculateStockCache(): void
    {
        $sum = $this->sumActiveVariantStock();
        $low = $this->low_stock_threshold ?? 5;

        $status = $sum <= 0
            ? 'sold_out'
            : ($sum <= $low ? 'low_stock' : 'in_stock');
        
        $this->forceFill([
            'total_product_stock' => $sum,
            'stock_status' => $status
        ])->save();
    }
    
    /* ─────────────────────────────────────────
     | Coupons
     ───────────────────────────────────────── */
    public function activeCoupons()
    {
        return $this->coupons()->where('is_active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    /* ─────────────────────────────────────────
     | Media (Spatie)
     ───────────────────────────────────────── */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (App::runningInConsole()) {
            return;
        }
        
        $this->addMediaConversion('sm_thumb')->fit(Fit::Contain, 150, 150)->format('webp')->nonQueued();
        $this->addMediaConversion('md_thumb')->fit(Fit::Contain, 300, 300)->format('webp')->nonQueued();
        $this->addMediaConversion('lg_thumb')->fit(Fit::Contain, 1080, 1080)->format('webp')->nonQueued();
    }
}
