<?php

use App\Http\Controllers\Api\BikeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
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

// Sanctum user placeholder (auth phase will expand this)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
