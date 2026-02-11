<?php

namespace App\Models;

use App\Models\Product;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Str;

class HomePage extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'tab_collections' => 'array',
        'rail_collection_ids' => 'array',
        'hero_slides' => 'array',
        'spotlight_tags' => 'array',
        'cta_button_link' => 'array'
    ];

    public function spotlightProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'spotlight_product_id');
    }

    public function getSpotlightDataAttribute(): array
    {
        if (! $this->relationLoaded('spotlightProduct') || ! $this->spotlightProduct) {
            return [];
        }

        return [
            'header' => $this->spotlight_header,
            'product' => $this->spotlightProduct,
            'title' => $this->spotlight_override_title ?? $this->spotlightProduct->name,
            'description' => $this->spotlight_override_description ?? $this->spotlightProduct->description,
            'tags' => $this->spotlight_tags ?? []
        ];
    }

    public function getSpotlightTagsViewAttribute(): array
    {
        return collect($this->spotlight_data ?? [])
            ->take(3)
            ->map(fn ($t) => [
                'icon' => (string) ($t['icon'] ?? ''),
                'label' => Str::limit((string) ($t['label'] ?? ''), 25, ''),
                'description' => Str::limit((string) ($t['description'] ?? ''), 40, '')
            ])
            ->filter(fn ($t) => $t['label'] !== '' || $t['description'] !== '')
            ->values()
            ->all();
    }

    public function registerMediaCollections(): void
    {
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
