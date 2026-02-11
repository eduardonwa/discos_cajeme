<?php

use App\Livewire\SearchPage;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuyNowController;
use App\Http\Controllers\CollectionController;

Route::get('/', \App\Livewire\StoreFront::class)->name('home');
Route::get('/product/{product}', \App\Livewire\Product::class)->name('product');
Route::get('/cart', \App\Livewire\Cart::class)->name('cart');
Route::get('/collections/all', [CollectionController::class, 'index'])->name('all-collections');
Route::get('/collections/{collection}', \App\Livewire\Collections::class)->name('collection');
Route::get('/search', SearchPage::class)->name('search');
Route::get('/guest/checkout-status', \App\Livewire\CheckoutStatus::class)->name('guest.checkout-status');

Route::post('/buy-now', BuyNowController::class)->name('buy-now');

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