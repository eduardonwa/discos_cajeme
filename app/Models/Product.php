<?php

namespace App\Models;

use App\Models\Image;
use App\Casts\MoneyCast;
use App\Models\CartItem;
use App\Models\Collection;
use Spatie\Image\Enums\Fit;
use App\Models\ProductVariant;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public $casts = [
        'price' => MoneyCast::class,
        'amount_tax' => MoneyCast::class,
        'amount_total' => MoneyCast::class,
        'amount_subtotal' => MoneyCast::class,
        'amount_discount' => MoneyCast::class,
        'is_admin' => 'boolean',
        'stock_status' => 'string',
        'low_stock_threshold' => 'integer'
    ];

    // relaciones

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

    public function cartItems()
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

    // getters
    public function getTotalStockAttribute()
    {
        return $this->variants->isNotEmpty() 
            ? $this->variants->sum('total_variant_stock') 
            : $this->total_product_stock;
    }

    public function getHasVariantsAttribute(): bool
    {
        return $this->variants()->exists();
    }

    public function getAvailableStock(): int
    {
        if ($this->has_variants) {
            return $this->variants->sum('total_variant_stock');
        }
        
        // Considerar lo que ya existe en el carrito
        $inCart = Auth::user()->cart?->items()
            ->where('product_id', $this->id)
            ->whereNull('product_variant_id')
            ->sum('quantity') ?? 0;
            
        return max(0, $this->total_product_stock - $inCart);
    }

    // helpers
    public function canFulfill(int $quantity): bool
    {
        return $this->getAvailableStock() >= $quantity;
    }

    public function isAvailable(): bool
    {
        return $this->getAvailableStock() > 0;
    }

    public function updateStockFromVariants()
    {
        if ($this->variants()->exists()) {
            $this->total_product_stock = $this->variants->sum('total_variant_stock');
        }
        $this->save();
    }

    public function activeCoupons()
    {
        return $this->coupons()->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            });
    }

    public function updateStockStatus()
    {
        if ($this->total_product_stock <= 0) {
            $this->stock_status = 'sold_out';
         } elseif ($this->total_stock <= $this->low_stock_threshold) {
            $this->stock_status = 'low_stock';
         } else {
            $this->stock_status = 'in_stock';
         }
         $this->save();
    }

    public function decreaseStock($quantity)
    {
        DB::transaction(function () use ($quantity) {
            if ($this->has_variants) {
                throw new \Exception("Use decreaseStock en las variantes");
            }
            $this->decrement('total_product_stock', $quantity);
            $this->updateStockStatus();
        });
    }

    // scopes
    public function scopePublished($q)
    {
        return $q->where('published', true);
    }

    // colecciones spatie
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')
            ->singleFile();
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {   
        $this
            ->addMediaConversion('sm_thumb')
            ->fit(Fit::Contain, 150, 150)
            ->format('webp')
            ->nonQueued();

        $this
            ->addMediaConversion('md_thumb')
            ->fit(Fit::Contain, 300, 300)
            ->format('webp')
            ->nonQueued();

        $this
            ->addMediaConversion('lg_thumb')
            ->fit(Fit::Contain, 1080, 1080)
            ->format('webp')
            ->nonQueued(); 
    }
}
