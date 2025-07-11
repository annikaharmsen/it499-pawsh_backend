<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdmin;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CheckoutController;

Route::post('test', function() {
    dd(route('checkout.stripe-webhook'));
});

// PUBLIC ROUTES

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::delete('delete-account/{user}', [AuthController::class, 'destroy'])->middleware('auth:sanctum');

Route::apiResource('cart', controller: CartItemController::class)->parameters(['cart' => 'cartItem'])->middleware('auth:sanctum');

Route::apiResource('products', controller: ProductController::class)->only([
    'index', 'show'
]);

Route::controller(CheckoutController::class)
->middleware('auth:sanctum')
->prefix('checkout')
->name('checkout.')
->group(function() {
    Route::post('/shipping/{order}', 'shippingAddress')->name('shipping');
    Route::post('/session/{order}', 'session')->name('session');
    Route::get('/status', 'status')->name('status');
    Route::post('/stripe-webhook', 'processSessionEvents')
        ->withoutMiddleware('auth:sanctum')->name('stripe-webhook');
});



Route::apiResource('orders', controller: OrderController::class)->middleware('auth:sanctum');

//Route::apiResource('payment_method', controller: PaymentMethodController::class)->middleware('auth:sanctum');

Route::apiResource('addresses', controller: AddressController::class)->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', IsAdmin::class])
    ->prefix('admin/dashboard')
    ->group(function () {
        Route::get('overview', [AdminDashboardController::class, 'overview']);
        Route::get('customers-report', [AdminDashboardController::class, 'usersReport']);
        Route::get('orders-report', [AdminDashboardController::class, 'ordersReport']);
        Route::get('products-report', [AdminDashboardController::class, 'productsReport']);
    });
