<?php

namespace App\Models;

use Spatie\Image\Enums\Fit;
use App\Models\AttributeVariant;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\DB;
use App\Models\ImageProductVariant;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

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

    public function decreaseStock(int $quantity): void
    {
        DB::transaction(function () use ($quantity) {
            if ($this->total_variant_stock < $quantity) {
                throw new \RuntimeException('No hay stock suficiente en la variante.');
            }

            $this->update([
                'total_variant_stock' => max($this->total_variant_stock - $quantity, 0)
            ]);
            
            // sincronizza y recalcula el padre
            $this->product->updateStockFromVariants();
            $this->product->refreshStockStatus();
        });
    }

    public function setIsActiveAttribute($value)
    {
        // No permitir activar si no hay stock
        if ($value && $this->total_variant_stock <= 0) {
            return;
        }
        
        // No permitir desactivar si hay stock positivo
        if (!$value && $this->total_variant_stock > 0) {
            throw new \Exception("No se puede desactivar una variante con stock disponible");
        }
        
        $this->attributes['is_active'] = $value;
    }

    public function canFulfillOrder(int $quantity): bool
    {
        return $this->total_variant_stock >= $quantity;
    }

    public function reserveStock(int $quantity): void
    {
        throw_if(
            !$this->canFulfillOrder($quantity),
            new \RuntimeException('No hay suficiente stock disponible')
        );
        
        $this->decrement('total_variant_stock', $quantity);
        $this->refresh();
        
        if ($this->total_variant_stock <= 0) {
            $this->update(['is_active' => false]);
        }
        
        $this->product->updateStockFromVariants();
    }

    public function isAvailable()
    {
        return $this->is_active && $this->total_variant_stock > 0;
    }

    public function canFulfill(int $quantity): bool
    {
        return $this->is_active && $this->total_variant_stock >= $quantity;
    }
}
