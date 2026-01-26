<?php

use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->onDelete('cascade');
            $table->string('stripe_checkout_session_id');
            $table->string('guest_email')->nullable();
            $table->string('guest_session_id')->nullable();
            $table->integer('amount_shipping')->default(0);
            $table->integer('amount_discount')->default(0);
            $table->integer('amount_tax')->default(0);
            $table->integer('amount_subtotal')->default(0);
            $table->integer('amount_total')->default(0);
            $table->json('billing_address');
            $table->json('shipping_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
