<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use App\Models\AttributeVariant;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;
use Database\Seeders\VariantSeeder;
use Database\Seeders\ProductCatalogSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            ProductCatalogSeeder::class,
            VariantSeeder::class
        ]);
        
        $adminRole = Role::where('name', 'admin')->first();

        $adminUser = User::factory()->create([
            'name' => 'eduardo',
            'email' => 'eduardo@hotmail.com',
            'password' => 'password',
        ]);

        $adminUser->assignRole($adminRole);

        /* $colorAttribute = Attribute::factory()->create(['key' => 'Color']);
        $sizeAttribute = Attribute::factory()->create(['key' => 'Tamaño']); */

/*         $products = Product::factory()
            ->count(10)
            ->create()
            ->each(function ($product) use ($colorAttribute, $sizeAttribute) {
                $variants = ProductVariant::factory()
                    ->count(rand(2, 5))
                    ->create(['product_id' => $product->id]);

                $variants->each(function ($variant) use ($colorAttribute, $sizeAttribute) {
                    AttributeVariant::factory()->create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $colorAttribute->id,
                        'value' => fake()->colorName(),
                    ]);

                    AttributeVariant::factory()->create([
                        'product_variant_id' => $variant->id,
                        'attribute_id' => $sizeAttribute->id,
                        'value' => fake()->randomElement(['S', 'M', 'L', 'XL']),
                    ]);
                });

                $product->update([
                    'total_product_stock' => $product->variants()->sum('total_variant_stock')
                ]);
            }); */
    }
}
