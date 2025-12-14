<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PoolUserController;

// Endpoint login (1.A)
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth.api')->group(function () {
    
    // Endpoint CRUD user login (1.B)
    Route::apiResource('users', UserController::class);
    
    // Endpoint (1.C, 1.D, 1.E)
    Route::prefix('data')->group(function() {
        Route::get('/search/name', [PoolUserController::class, 'searchByName']);
        Route::get('/search/nim', [PoolUserController::class, 'searchByNim']);
        Route::get('/search/ymd', [PoolUserController::class, 'searchByYmd']);
        Route::get('/all', [PoolUserController::class, 'getAllData']);
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});