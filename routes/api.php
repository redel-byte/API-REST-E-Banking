<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SwaggerController;
use Illuminate\Support\Facades\Route;

// Swagger Documentation Routes
Route::get('/docs/test', [SwaggerController::class, 'test']);
Route::get('/docs/ui', [SwaggerController::class, 'ui']);
Route::get('/docs/json', [SwaggerController::class, 'generate']);

// Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:api')->group(function () {
    // User Profile
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'update']);
    Route::patch('/me/password', [AuthController::class, 'updatePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Account Management
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
    Route::post('/accounts/{id}/co-owners', [AccountController::class, 'addCoOwner']);
    Route::delete('/accounts/{id}/co-owners/{userId}', [AccountController::class, 'removeCoOwner']);
    Route::post('/accounts/{id}/guardian', [AccountController::class, 'assignGuardian']);
    Route::patch('/accounts/{id}/convert', [AccountController::class, 'convert']);
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);

    // Transfer Management
    Route::post('/transfers', [TransferController::class, 'store']);
    Route::get('/transfers/{id}', [TransferController::class, 'show']);
    Route::get('/accounts/{id}/transactions', [TransferController::class, 'accountTransactions']);
    Route::get('/transactions/{id}', [TransferController::class, 'transactionDetails']);
});

// Admin Routes
Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
    Route::get('/accounts', [AdminController::class, 'getAllAccounts']);
    Route::patch('/accounts/{id}/block', [AdminController::class, 'blockAccount']);
    Route::patch('/accounts/{id}/unblock', [AdminController::class, 'unblockAccount']);
    Route::patch('/accounts/{id}/close', [AdminController::class, 'closeAccount']);
});