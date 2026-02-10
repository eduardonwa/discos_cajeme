<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeVariant;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = config('catalog');

        // 1) Colecciones
        $collectionsBySlug = collect($catalog['collections'] ?? [])
            ->mapWithKeys(function (array $c) {
                $model = Collection::updateOrCreate(
                    ['slug' => $c['slug']],
                    [
                        'name' => $c['name'],
                        'description' => $c['description'],
                    ]
                );

                return [$c['slug'] => $model];
            });

        // 2) Productos (sin eventos de Product) + media (con eventos)
        foreach (($catalog['products'] ?? []) as $p) {

            // Defaults seguros
            $p = array_merge([
                'published' => true,
                'low_stock_threshold' => 5,
                'cached_quantity_sold' => 0,
            ], $p);

            $p['slug'] = $p['slug'] ?? Str::slug($p['name']);

            /** @var Product $product */
            $product = null;

            Product::withoutEvents(function () use (&$product, $p, $collectionsBySlug) {

                $product = Product::updateOrCreate(
                    ['slug' => $p['slug']],
                    Arr::only($p, [
                        'name',
                        'slug',
                        'description',
                        'cover_img_alt',
                        'promo_label',
                        'published',
                        'buy_now_enabled',
                        'total_product_stock',
                        'stock_status',
                        'low_stock_threshold',
                        'cached_quantity_sold',
                    ])
                );

                // 3) Pivot con colecciones
                $ids = collect($p['collections'] ?? [])
                    ->map(fn (string $slug) => $collectionsBySlug[$slug]->id ?? null)
                    ->filter()
                    ->values()
                    ->all();

                if (!empty($ids)) {
                    $product->collections()->sync($ids);
                }

                // 4) Variantes (copiar tal cual del catálogo)
                $product->variants()->delete();

                $variants = $p['variants'] ?? [];

                // Fallback defensivo
                if (empty($variants)) {
                    $variants = [[
                        'title' => 'Default title',
                        'price' => 0,
                        'compare_at_price' => null,
                        'total_variant_stock' => (int)($p['total_product_stock'] ?? 0),
                        'is_default' => true,
                        'is_active' => ((int)($p['total_product_stock'] ?? 0) > 0),
                        'attributes' => [],
                    ]];
                }

                // Si nadie marcó default, el primero será default
                $hasDefault = collect($variants)->contains(
                    fn ($v) => (bool)($v['is_default'] ?? false)
                );

                foreach ($variants as $i => $v) {
                    $variant = $product->variants()->create([
                        'title' => $v['title'] ?? ('Variant ' . ($i + 1)),
                        'price' => (int)($v['price'] ?? 0),
                        'compare_at_price' => $v['compare_at_price'] ?? null,
                        'total_variant_stock' => (int)($v['total_variant_stock'] ?? 0),
                        'is_default' => (bool)($v['is_default'] ?? (!$hasDefault && $i === 0)),
                        'is_active' => (bool)($v['is_active'] ?? (((int)($v['total_variant_stock'] ?? 0)) > 0)),
                    ]);

                    // 5) Atributos por variante
                    foreach (($v['attributes'] ?? []) as $key => $value) {
                        $attr = Attribute::firstOrCreate(['key' => $key]);

                        AttributeVariant::updateOrCreate(
                            [
                                'product_variant_id' => $variant->id,
                                'attribute_id' => $attr->id,
                            ],
                            [
                                'value' => (string) $value,
                            ]
                        );
                    }
                }
            });

            $product->clearMediaCollection('featured');

            $featuredPath = ($p['images'][0] ?? null);

            if ($featuredPath) {
                $full = public_path($featuredPath);

                if (is_file($full)) {
                    $product->addMedia($full)
                        ->preservingOriginal()
                        ->toMediaCollection('featured');
                } else {
                    $this->command?->warn("No existe archivo: {$full} (slug: {$product->slug})");
                }
            }
        }
    }
}
