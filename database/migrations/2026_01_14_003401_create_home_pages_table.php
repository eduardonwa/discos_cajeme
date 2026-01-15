<?php

use App\Models\Product;
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
        Schema::create('home_pages', function (Blueprint $table) {
            $table->id();

            // 1) HERO
            $table->string('hero_image_alt')->nullable();
            $table->string('hero_img_link')->nullable();
            $table->json('hero_video')->nullable();
            
            // 2) TAB COLLECTIONS
            $table->json('tab_collections')->nullable();
            $table->string('tab_collection_header')->nullable();
            $table->unsignedTinyInteger('tab_products_limit')->default(6);

            // 3) CTA
            $table->string('cta_bg_img_alt')->nullable();
            $table->string('cta_button')->nullable();
            $table->string('cta_button_link')->nullable();
            $table->string('cta_header')->nullable();
            $table->text('cta_description')->nullable();

            // 4) SPOTLIGHT (featured)
            $table->foreignId('spotlight_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('spotlight_header')->nullable();
            $table->string('spotlight_override_title')->nullable();
            $table->text('spotlight_override_description')->nullable();

            // 5) COLLECTIONS
            $table->json('rail_collection_ids')->nullable();
            $table->string('rail_collection_header')->nullable();
            $table->string('rail_collection_description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_pages');
    }
};
