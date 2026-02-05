<?php

namespace App\Models;

use App\Models\Product;
use Spatie\Image\Enums\Fit;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Collection extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    public function products()
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function featuredProduct() {
        return $this->belongsTo(Product::class, 'featured_product_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('col_thumbnail');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (App::runningInConsole()) {
            return;
        }

        $this->addMediaConversion('sm_thumb')->fit(Fit::Contain, 150, 150)->format('webp')->performOnCollections('col_thumbnail')->nonQueued();
        $this->addMediaConversion('md_thumb')->fit(Fit::Contain, 300, 300)->format('webp')->performOnCollections('col_thumbnail')->nonQueued();
    }
}
