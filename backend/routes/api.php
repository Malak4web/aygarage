<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BikeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CustomerAddressController;
use App\Http\Controllers\Api\CustomerBikeController;
use App\Http\Controllers\Api\CustomerFavoriteController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SellRequestController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Catalog API
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/products/{slug}/reviews', [ProductController::class, 'reviews']);
Route::get('/bikes', [BikeController::class, 'index']);
Route::get('/bikes/{slug}', [BikeController::class, 'show']);
Route::get('/services', [ServiceController::class, 'index']);
Route::post('/coupons/check', [CouponController::class, 'check']);

// Public Auth & Lead Generation (Separate rate limiters per IP)
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/sell-requests', [SellRequestController::class, 'store'])->middleware('throttle:5,1');

// Authenticated Customer API
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/me', [AuthController::class, 'updateMe']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Bikes
    Route::get('/me/bikes', [CustomerBikeController::class, 'index']);
    Route::post('/me/bikes', [CustomerBikeController::class, 'store']);
    Route::put('/me/bikes/{id}', [CustomerBikeController::class, 'update']);
    Route::delete('/me/bikes/{id}', [CustomerBikeController::class, 'destroy']);

    // Addresses
    Route::get('/me/addresses', [CustomerAddressController::class, 'index']);
    Route::post('/me/addresses', [CustomerAddressController::class, 'store']);
    Route::put('/me/addresses/{id}', [CustomerAddressController::class, 'update']);
    Route::delete('/me/addresses/{id}', [CustomerAddressController::class, 'destroy']);

    // Favorites
    Route::get('/me/favorites', [CustomerFavoriteController::class, 'index']);
    Route::post('/me/favorites/{identifier}', [CustomerFavoriteController::class, 'store']);
    Route::delete('/me/favorites/{identifier}', [CustomerFavoriteController::class, 'destroy']);

    // Orders
    Route::get('/me/orders', [OrderController::class, 'index']);
    Route::get('/me/orders/{number}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
});
