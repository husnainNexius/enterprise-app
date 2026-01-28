<?php

use Illuminate\Support\Facades\Route;



Route::post('/register', [\App\Http\Controllers\UserController::class, 'registerUser']);
Route::post('/login', [\App\Http\Controllers\UserController::class, 'UserLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/{id}', [\App\Http\Controllers\UserController::class, 'getUserDetails']);
});

// Users API (for dropdowns)
Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);

// Product routes
Route::apiResource('products', \App\Http\Controllers\ProductController::class);

// Order routes - additional routes first to avoid conflicts
Route::prefix('orders')->group(function () {
    Route::get('{id}/items', [\App\Http\Controllers\OrderController::class, 'items']);
    Route::get('{id}/history', [\App\Http\Controllers\OrderController::class, 'history']);
    Route::post('calculate-totals', [\App\Http\Controllers\OrderController::class, 'calculateTotals']);
    Route::get('statistics', [\App\Http\Controllers\OrderController::class, 'statistics']);
    Route::put('{id}/apply-discount', [\App\Http\Controllers\OrderController::class, 'applyDiscount']);
    Route::post('{id}/process', [\App\Http\Controllers\OrderController::class, 'process']);
});

// Status route - separate to avoid conflicts
Route::put('orders/{id}/status', [\App\Http\Controllers\OrderController::class, 'updateStatus']);

// Order resource routes (must be after specific routes)
Route::apiResource('orders', \App\Http\Controllers\OrderController::class);
