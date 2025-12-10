<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

Route::get('/', [StoreController::class, 'index']);
Route::post('/cart/add', [StoreController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/remove', [StoreController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/shipping-options', [StoreController::class, 'getShippingOptions'])->name('shipping.options');
Route::post('/checkout', [StoreController::class, 'checkout'])->name('checkout');
