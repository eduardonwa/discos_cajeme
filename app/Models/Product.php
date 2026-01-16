<?php

namespace App\Models;

use Money\Money;
use App\Casts\MoneyCast;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
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
        'price'                 => MoneyCast::class,
        'compare_at_price'      => MoneyCast::class,
        'amount_tax'            => MoneyCast::class,
        'amount_total'          => MoneyCast::class,
        'amount_subtotal'       => MoneyCast::class,
        'amount_discount'       => MoneyCast::class,
        'is_admin'              => 'boolean',
        'stock_status'          => 'string',
        'low_stock_threshold'   => 'integer',
    ];

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

    // ¿Tiene variantes?
    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->exists();
    }

    // Stock total “computado” (si hay variantes: suma variantes; si no: campo manual)
    public function getComputedTotalStockAttribute(): int
    {
        if ($this->getHasVariantsAttribute()) {
            return (int) ($this->variants()->sum('total_variant_stock') ?? 0);
        }
        return (int) ($this->total_product_stock ?? 0);
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

    // Sincroniza el campo manual con variantes (si existen)
    public function syncTotalStockFromVariants(): void
    {
        if (! $this->getHasVariantsAttribute()) {
            return;
        }

        $sum = (int) $this->variants()->sum('total_variant_stock');
        if ((int) $this->total_product_stock !== $sum) {
            $this->total_product_stock = $sum;
            $this->save();
        }
    }

    // Actualiza estado de stock según umbrales (usa computed)
    public function refreshStockStatus(): void
    {
        $total = $this->computed_total_stock;
        $low   = $this->low_stock_threshold ?? 5;

        $this->stock_status = $total <= 0
            ? 'sold_out'
            : ($total <= $low ? 'low_stock' : 'in_stock');

        $this->save();
    }

    // Decremento simple para productos SIN variantes (atomic)
    public function decreaseStock(int $quantity): void
    {
        if ($this->getHasVariantsAttribute()) {
            throw new \LogicException('Use decreaseStock() en la variante.');
        }

        DB::transaction(function () use ($quantity) {
            // bloquea la fila para consistencia
            $fresh = self::whereKey($this->id)->lockForUpdate()->first();
            if ($fresh->total_product_stock < $quantity) {
                throw new \RuntimeException('No hay stock suficiente.');
            }
            $fresh->decrement('total_product_stock', $quantity);
            $fresh->refreshStockStatus();
        });
    }
    
    public function updateStockFromVariants(): void
    {
        $this->syncTotalStockFromVariants();
        $this->refreshStockStatus();
    }

    /* ─────────────────────────────────────────
     | Pricing / Discounts (si quieres helpers aquí)
     ───────────────────────────────────────── */
    public function getHasDiscountAttribute(): bool
    {
        return $this->compare_at_price !== null
            && $this->price !== null
            && $this->compare_at_price->greaterThan($this->price);
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->has_discount) return null;
        $p = $this->price->getAmount();
        $c = $this->compare_at_price->getAmount();
        return (int) round((1 - ($p / $c)) * 100);
    }

    // 👉 NUEVO: precio “antes” (tachado) o null
    public function getOriginalPriceAttribute(): ?Money
    {
        return $this->has_discount ? $this->compare_at_price : null;
    }

    // 👉 NUEVO: precio final (el que se cobra hoy)
    public function getFinalPriceAttribute(): Money
    {
        return $this->price;
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
        $this->addMediaConversion('sm_thumb')->fit(Fit::Contain, 150, 150)->format('webp')->nonQueued();
        $this->addMediaConversion('md_thumb')->fit(Fit::Contain, 300, 300)->format('webp')->nonQueued();
        $this->addMediaConversion('lg_thumb')->fit(Fit::Contain, 1080, 1080)->format('webp')->nonQueued();
    }
}
