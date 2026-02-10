<?php

namespace App\Models;

use Money\Money;
use App\Casts\MoneyCast;
use Spatie\Image\Enums\Fit;
use App\Models\AttributeVariant;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $casts = [
        'price'            => MoneyCast::class,
        'compare_at_price' => MoneyCast::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributes()
    {
        return $this->hasMany(AttributeVariant::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product-variant-image')
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {   
        $this
            ->addMediaConversion('sm_thumb')
            ->fit(Fit::Contain, 150, 150)
            ->format('webp')
            ->nonQueued();

        $this
            ->addMediaConversion('lg_thumb')
            ->fit(Fit::Contain, 1080, 1080)
            ->format('webp')
            ->nonQueued(); 
    }

    public function adjustStock(int $delta): void
    {
        DB::transaction(function () use ($delta) {
            // lock row
            $fresh = self::whereKey($this->id)->lockForUpdate()->first();

            $new = $fresh->total_variant_stock + $delta;

            if ($new < 0) {
                throw new \RuntimeException('No hay stock suficiente en la variante.');
            }

            $fresh->total_variant_stock = $new;
            $fresh->is_active = $new > 0;
            $fresh->save();

            // recalcula producto
            $fresh->product->recalculateStockCache();
        });
    }
    
    public function canFulfill(int $quantity): bool
    {
        return $this->is_active && $this->total_variant_stock >= $quantity;
    }

    public function canFulfillOrder(int $quantity): bool
    {
        return $this->total_variant_stock >= $quantity;
    }

    public function isAvailable()
    {
        return $this->is_active && $this->total_variant_stock > 0;
    }

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

    // precio “antes” (tachado) o null
    public function getOriginalPriceAttribute(): ?Money
    {
        return $this->has_discount ? $this->compare_at_price : null;
    }

    // precio final (el que se cobra hoy)
    public function getFinalPriceAttribute(): Money
    {
        return $this->price;
    }
}
