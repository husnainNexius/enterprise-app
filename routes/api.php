<?php

use Illuminate\Support\Facades\Route;



Route::post('/register', [\App\Http\Controllers\UserController::class, 'registerUser']);
Route::post('/login', [\App\Http\Controllers\UserController::class, 'UserLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/{id}', [\App\Http\Controllers\UserController::class, 'getUserDetails']);
});
