<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\SearchHistoryController;

// User Authentication Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/verify', [UserController::class, 'confirmVerificationCode']);
Route::post('/login', [UserController::class, 'login']);

// Vehicle Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/vehicles/{vin}', [VehicleController::class, 'decodeVin']);
});

// Search History Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/search-history', [SearchHistoryController::class, 'index']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
