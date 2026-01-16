<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = config('catalog');

        // 1) Colecciones
        $collectionsBySlug = collect($catalog['collections'] ?? [])
            ->mapWithKeys(function ($c) {
                $model = Collection::firstOrCreate(
                    ['slug' => $c['slug']],
                    [
                        'name' => $c['name'],
                        'description' => $c['description'],
                    ],
                );
                return [$c['slug'] => $model];
            });
        
            // 2) Productos
            foreach ($catalog['products'] ?? [] as $p) {
                // Defaults seguros
                $p = array_merge([
                    'published' => true,
                    'low_stock_threshold' => 5,
                    'cached_quantity_sold' => 0,
                ], $p);

            $p['slug'] = $p['slug'] ?? Str::slug($p['name']);

            // Calcular stock_status si falta
            if (empty($p['stock_status'])) {
                $qty = (int) ($p['total_product_stock'] ?? 0);
                $thr = (int) ($p['low_stock_threshold']);
                $p['stock_status'] = $qty === 0 ? 'sold_out' : ($qty <= $thr ? 'low_stock' : 'in_stock');
            }

            // Crea/actualiza producto
            /** @var Product $product */
            $product = Product::updateOrCreate(
                ['slug' => $p['slug']],
                    Arr::only($p, [
                        'name','slug','description','price','published',
                        'total_product_stock','stock_status',
                        'low_stock_threshold','cached_quantity_sold',
                    ])
                );
            
            // 3) Pivot con colecciones
            $ids = collect($p['collections'] ?? [])
                ->map(fn ($slug) => $collectionsBySlug[$slug]->id ?? null)
                ->filter()->values()->all();
            if (!empty($ids)) {
                $product->collections()->sync($ids);
            }

            // 4) Importar imagenes a Spatie (desde public/catalog/)
            $product->clearMediaCollection('featured');

            foreach (($p['images'] ?? []) as $path) {
                $full = public_path($path);
                if(is_file($full)) {
                    $product->addMedia($full)
                        ->preservingOriginal()
                        ->toMediaCollection('featured');
                }
            }
        }
    }
}
