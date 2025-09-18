<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('price');
            $table->unsignedInteger('compare_at_price')->nullable();
            $table->string('promo_label')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('buy_now_enabled')->default(false);
            $table->unsignedInteger('total_product_stock')->default(0);
            $table->enum('stock_status', ['in_stock', 'low_stock', 'sold_out'])->default('in_stock');
            $table->integer('low_stock_threshold')->default(5);
            $table->unsignedBigInteger('cached_quantity_sold')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
