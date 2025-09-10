<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeVariant;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Str;

class VariantSeeder extends Seeder
{
    /** Paletas base */
    private array $COLORS = ['Negro', 'Blanco', 'Gris', 'Beige', 'Oliva', 'Azul Marino', 'Arena', 'Terracota'];
    private array $SIZES  = ['XS','S','M','L','XL'];
    
    /** forzar perfil por slug */
    private array $FORCE_PROFILE_BY_SLUG = [
        'essential-tee' => 'size_only',
        'minimal-classic-bag' => 'none',
        'run-black' => 'size_only',
        'olive-wash' => 'size_only',
        'heart-only' => 'size_only',
        'pure-step' => 'size_only',
        'cloud-grey' => 'size_only',
    ];

    /** excluir por slug (no generar variantes) */
    private array $EXCLUDE_BY_SLUG = [
        'luz-dorada-chain' => true,
    ];

    /** pesos del random-pick de perfil */
    private array $PROFILE_WEIGHTS = [
        'none'              => 0.15,
        'color_only'        => 0.35,
        'size_only'         => 0.25,
        'color_and_size'    => 0.25
    ];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // asegura atributos base
        $attrColor = Attribute::firstOrCreate(['key' => 'Color']);
        $attrSize = Attribute::firstOrCreate(['key' => 'Tamaño']);

        Product::query()->each(function (Product $product) use ($attrColor, $attrSize) {
            $slug = $product->slug ?? Str::slug($product->name);

            // excluir productos si aplica
            if (isset($this->EXCLUDE_BY_SLUG[$slug])) {
                return;
            }

            // perfil: forzado o aleatorio con pesos
            $profile = $this->FORCE_PROFILE_BY_SLUG[$slug] ?? $this->pickProfile();

            // si el perfil es "none", NO tocamos las variantes ni totales del producto
            if ($profile === "none") {
                return;
            }

            // limpiar variantes previas
            $product->variants()->delete();

            // genera variantes segun perfil
            $sumStock = 0;

            switch ($profile) {
                case 'color_only':
                    // numero de colores distintos
                    $colors = collect($this->COLORS)->shuffle()->take(rand(2, 5))->values();
                    foreach ($colors as $color) {
                        $stock = rand(0, 12);
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'total_variant_stock' => $stock,
                            'is_active' => true,
                        ]);
                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrColor->id,
                            'value' => $color,
                        ]);
                        $sumStock += $stock;
                    }
                    break;

                case 'size_only':
                    // tallas distintas
                    $sizes = collect($this->SIZES)->shuffle()->take(rand(2,5))->values();
                    foreach ($sizes as $size) {
                        $stock = rand(0, 12);
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'total_variant_stock' => $stock,
                            'is_active' => true,
                        ]);
                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrSize->id,
                            'value' => $size,
                        ]);
                        $sumStock += $stock;
                    }
                    break;

                case 'color_and_size':
                    //combinaciones unicas colorxtalla
                    $colors = collect($this->COLORS)->shuffle()->take(rand(2, 4))->values();
                    $sizes  = collect($this->SIZES)->shuffle()->take(rand(2, 4))->values();
                    
                    // elige cuantas combinaciones crear
                    $combos = [];
                    foreach($colors as $c) {
                        foreach ($sizes as $s) {
                            $combos[] = [$c, $s];
                        }
                    }
                    shuffle($combos);
                    $n = min(count($combos), rand(3, 8));

                    for ($i = 0; $i < $n; $i++) {
                        [$c, $s] = $combos[$i];
                        $stock = rand(0, 12);
                        
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'total_variant_stock' => $stock,
                            'is_active' => true,
                        ]);

                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrColor->id,
                            'value' => $c,
                        ]);
                        AttributeVariant::create([
                            'product_variant_id' => $variant->id,
                            'attribute_id' => $attrSize->id,
                            'value' => $s,
                        ]);

                        $sumStock += $stock;
                    }
                    break;
                }

                // actualiza producto solo si creamos variantes
                if ($sumStock > 0 || $product->variants()->exists()) {
                    $thr = (int) $product->low_stock_threshold;
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

    /** selección aleatoria por pesos */
    private function pickProfile(): string
    {
        $r = mt_rand() / mt_getrandmax();
        $acc = 0;
        foreach ($this->PROFILE_WEIGHTS as $profile => $weight) {
            $acc += $weight;
            if ($r <= $acc) return $profile;
        }
        return 'color_only';
    }
}
