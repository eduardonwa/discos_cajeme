<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeVariant;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VariantSeeder extends Seeder
{
    private array $VINYL_COLORS = [
        'Negro', 'Transparente', 'Rojo', 'Azul', 'Verde', 'Marmoleado', 'Splatter',
    ];

    private array $EDITIONS = [
        'Standard', 'Remastered', 'Anniversary', 'Limited',
    ];

    /** Forzar perfil por slug si quieres casos específicos */
    private array $FORCE_PROFILE_BY_SLUG = [
        // Ejemplos:
        'iron-maiden-powerslave' => 'vinyl_color_and_edition',
        'metallica-master-of-puppets' => 'vinyl_color_only',
        'death-symbolic' => 'vinyl_edition_only'
    ];

    /** Excluir por slug (no generar variantes) */
    private array $EXCLUDE_BY_SLUG = [
        // 'algún-slug' => true,
    ];

    private function ensureDefaultVariant(Product $product, array $attrs = []): void
    {
        // Si ya tiene variates, no hacemos nada
        if ($product->variants()->exists()) {
            return;
        }

        $stock = (int) ($product->total_product_stock ?? 0);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'total_variant_stock' => $stock,
            'is_active' => $stock > 0
        ]);

        foreach ($attrs as $key => $value) {
            $attr = Attribute::firstOrCreate(['key' => $key]);
            AttributeVariant::create([
                'product_variant_id' => $variant->id,
                'attribute_id' => $attr->id,
                'value' => $value
            ]);
        }

        // actualizar cache del producto
        $thr = (int) ($product->low_stock_threshold ?? 5);
        $status = $stock === 0
            ? 'sold_out'
            : ($stock <= $thr ? 'low_stock' : 'in_stock');
        
        $product->update([
            'total_product_stock' => $stock,
            'stock_status' => $status
        ]);
    }

    public function run(): void
    {
        // Atributos base para vinilos
        $attrEdition    = Attribute::firstOrCreate(['key' => 'Edición']);
        $attrVinylColor = Attribute::firstOrCreate(['key' => 'Color de Vinilo']);

        Product::query()->each(function (Product $product) use ($attrEdition, $attrVinylColor) {
            $slug = $product->slug ?? Str::slug($product->name);

            if (isset($this->EXCLUDE_BY_SLUG[$slug])) {
                return;
            }

            // Detectar si es vinilo por colección
            $isVinyl = $product->collections()
                ->where('slug', 'vinyl')
                ->exists();

            // CDs: no variantes
            if (!$isVinyl) {
                $this->ensureDefaultVariant($product, [
                    'Formato' => 'CD'
                ]);
                return;
            }

            // Perfil: forzado o aleatorio para VINYL
            $profile = $this->FORCE_PROFILE_BY_SLUG[$slug] ?? $this->pickVinylProfile();

            // Si none: no tocar variantes
            if ($profile === 'none') {
                $this->ensureDefaultVariant($product, [
                    'Formato' => 'Vinyl',
                    'Color de Vinilo' => 'Negro',
                    'Edición' => 'Standard'
                ]);
                return;
            }

            // Limpiar variantes previas
            $product->variants()->delete();

            $sumStock = 0;

            switch ($profile) {
                case 'vinyl_color_only': {
                    $colors = collect($this->VINYL_COLORS)->shuffle()->take(rand(2, 4))->values();
                    foreach ($colors as $color) {
                        $stock = rand(0, 8);

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'total_variant_stock' => $stock,
                            'is_active' => true,
                        ]);

                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrVinylColor->id,
                            'value' => $color,
                        ]);

                        $sumStock += $stock;
                    }
                    break;
                }

                case 'vinyl_edition_only': {
                    $editions = collect($this->EDITIONS)->shuffle()->take(rand(2, 3))->values();
                    foreach ($editions as $edition) {
                        $stock = rand(0, 8);

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'total_variant_stock' => $stock,
                            'is_active' => true,
                        ]);

                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrEdition->id,
                            'value' => $edition,
                        ]);

                        $sumStock += $stock;
                    }
                    break;
                }

                case 'vinyl_color_and_edition': {
                    $colors = collect($this->VINYL_COLORS)->shuffle()->take(rand(2, 3))->values();
                    $editions = collect($this->EDITIONS)->shuffle()->take(rand(2, 3))->values();

                    $combos = [];
                    foreach ($colors as $c) {
                        foreach ($editions as $e) {
                            $combos[] = [$c, $e];
                        }
                    }
                    shuffle($combos);

                    $n = min(count($combos), rand(2, 5));
                    for ($i = 0; $i < $n; $i++) {
                        [$c, $e] = $combos[$i];
                        $stock = rand(0, 8);

                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'total_variant_stock' => $stock,
                            'is_active' => true,
                        ]);

                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrVinylColor->id,
                            'value' => $c,
                        ]);

                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrEdition->id,
                            'value' => $e,
                        ]);

                        $sumStock += $stock;
                    }
                    break;
                }
            }

            // Actualiza stock total del producto basado en variantes
            if ($sumStock > 0 || $product->variants()->exists()) {
                $thr = (int) ($product->low_stock_threshold ?? 5);
                $status = $sumStock === 0
                    ? 'sold_out'
                    : ($sumStock <= $thr ? 'low_stock' : 'in_stock');

                $product->update([
                    'total_product_stock' => $sumStock,
                    'stock_status' => $status,
                ]);
            }
        });
    }

    private function pickVinylProfile(): string
    {
        // Perfil “realista”: a veces solo negro (sin variantes), a veces ediciones, a veces color/edición
        $weights = [
            'none' => 0.35,
            'vinyl_color_only' => 0.35,
            'vinyl_edition_only' => 0.20,
            'vinyl_color_and_edition' => 0.10,
        ];

        $r = mt_rand() / mt_getrandmax();
        $acc = 0;

        foreach ($weights as $profile => $w) {
            $acc += $w;
            if ($r <= $acc) return $profile;
        }

        return 'none';
    }
}
