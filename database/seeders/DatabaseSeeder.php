<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
    }
}
