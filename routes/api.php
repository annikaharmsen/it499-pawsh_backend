<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

Route::apiResource('user', controller: UserController::class);

Route::apiResource('cart_item', controller: CartItemController::class);

Route::apiResource('product', controller: ProductController::class);

Route::apiResource('order_item', controller: OrderItemController::class);

Route::apiResource('payment', controller: PaymentController::class);

Route::apiResource('order', controller: OrderController::class);

Route::apiResource('payment_method', controller: PaymentMethodController::class);

Route::apiResource('address', controller: AddressController::class);
