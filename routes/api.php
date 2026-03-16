<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SwaggerController;
use Illuminate\Support\Facades\Route;

// Swagger Documentation Routes
Route::get('/docs/test', [SwaggerController::class, 'test']);
Route::get('/docs/ui', [SwaggerController::class, 'ui']);
Route::get('/docs/json', [SwaggerController::class, 'generate']);

// Authentication Routes
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

// Protected Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});