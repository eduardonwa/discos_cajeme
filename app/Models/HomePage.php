<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomePage extends Model
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'tab_collection_ids' => 'array',
        'rail_collection_ids' => 'array',
    ];

    public function spotlightProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'spotlight_product_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('home_hero_img')->singleFile();
        $this->addMediaCollection('home_cta_img')->singleFile();
        $this->addMediaCollection('home_spotlight_override')->singleFile();
    }
}
