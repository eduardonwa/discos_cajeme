<?php

use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\StoreFront::class)->name('home');
Route::get('/product/{product}', \App\Livewire\Product::class)->name('product');
Route::get('/cart', \App\Livewire\Cart::class)->name('cart');
Route::get('/collections/{collection}', \App\Livewire\Collections::class)->name('collection');

Route::get('/preview', function() {
    $order = \App\Models\Order::with('items.variant.attributes')->first();

    return new OrderConfirmation($order);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/checkout-status', \App\Livewire\CheckoutStatus::class)->name('checkout-status');
    Route::get('/order/{orderId}', \App\Livewire\ViewOrder::class)->name('view-order');
    Route::get('/my-orders', \App\Livewire\MyOrders::class)->name('my-orders');
    Route::get('/notifications/all', \App\Livewire\NotificationsList::class)->name('notifications');
});