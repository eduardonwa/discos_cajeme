<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cart::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Product::class)->nullable()->constrained()->onDelete('cascade');
            $table->foreignIdFor(ProductVariant::class)->constrained()->onDelete('cascade');
            $table->unique(['cart_id', 'product_variant_id']);
            $table->string('coupon_code', 32)->nullable();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
