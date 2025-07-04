<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// TEST ROUTES
Route::post('/__reset', function () {
    if (!App::environment('local', 'testing')) {
        abort(403, 'Unauthorized.');
    }

    Artisan::call('migrate:fresh --seed');

    return response()->json(['message' => 'Database reset.']);
});

// PUBLIC ROUTES

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::delete('delete-account/{user}', [AuthController::class, 'destroy'])->middleware('auth:sanctum');

Route::apiResource('cart', controller: CartItemController::class)->parameters(['cart' => 'cartItem'])->middleware('auth:sanctum');

Route::apiResource('products', controller: ProductController::class)->only([
    'index', 'show'
]);

Route::apiResource('payment', controller: PaymentController::class)->middleware('auth:sanctum');

Route::apiResource('orders', controller: OrderController::class)->middleware('auth:sanctum');

//Route::apiResource('payment_method', controller: PaymentMethodController::class)->middleware('auth:sanctum');

Route::apiResource('addresses', controller: AddressController::class)->middleware('auth:sanctum');

Route::get('stripe-return', [PaymentController::class, 'store']);
