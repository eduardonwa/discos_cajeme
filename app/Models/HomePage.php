<?php

namespace App\Models;

use App\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HomePage extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'tab_collections' => 'array',
        'rail_collection_ids' => 'array',
        'hero_slides' => 'array'
    ];

    public function spotlightProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'spotlight_product_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('home_cta_img')->singleFile();
        $this->addMediaCollection('home_spotlight_override')->singleFile();
        $this->addMediaCollection('hero_1')->singleFile();
        $this->addMediaCollection('hero_2')->singleFile();
        $this->addMediaCollection('hero_3')->singleFile();
        $this->addMediaCollection('hero_4')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('hero_sm')
            ->width(900)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();

        $this->addMediaConversion('hero_md')
            ->width(1400)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();
            
        $this->addMediaConversion('hero_lg')
            ->width(2400)
            ->sharpen(10)
            ->format('webp')
            ->nonQueued();
    }
}
