<?php

namespace App\Models;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;
    
    protected $touches = ['cart'];

    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: function() {
                return $this->variant->product->price->multiply($this->quantity);
            }
        );
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // Accesor para obtener el producto resolviendo la relación
    public function getResolvedProductAttribute()
    {
        return $this->variant?->product;
    }

    // Accesor para la descripción
    public function getDescriptionAttribute()
    {
        if ($this->product_variant_id && $this->variant) {
            return $this->variant->attributes->map(function($attr) {
                return "{$attr->attribute->key}: {$attr->value}";
            })->implode(', ');
        }
        return 'Producto estándar';
    }
}
