<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PartsController;
use App\Http\Controllers\Api\StocksController;
use App\Http\Controllers\Api\SupplierApiController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Default user info
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth Routes (public login/register, protected logout)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Protected Routes Group
Route::middleware('auth:sanctum')->group(function () {
    // Items
    Route::get('/items', [PartsController::class, 'index']);
    Route::post('/items', [PartsController::class, 'store']);  // Ubah ke plural biar konsisten

    // Stocks
    Route::get('/stocks/{bpid}', [StocksController::class, 'index']);
    Route::get('/stocks', [StocksController::class, 'allStocks']);
    Route::post('/stocks', [StocksController::class, 'store']);  // Ubah ke plural

    // Suppliers
    Route::post('/suppliers', [SupplierApiController::class, 'store']);  // Ubah ke plural

    // Orders (POST & GET di sini)
    Route::post('/orders', [OrderController::class, 'store']);  // Hapus duplikasi /order
    Route::get('/orders', [OrderController::class, 'index']);   // Fetch list
});
