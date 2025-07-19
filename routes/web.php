<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ChekoutController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CategoryController;;

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DahsboardController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['web', 'auth', 'role:manager,kasir'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard-summary', [DahsboardController::class, 'getSummary']);
    Route::get('/penjualan-perbulan', [DahsboardController::class, 'getSalesPerMonth']);
    Route::get('/tranksaksi-terbaru', [DahsboardController::class, 'getRecentTransaction']);

    Route::apiResource('products', ProductController::class);
    Route::apiResource('category', CategoryController::class);
    Route::apiResource('supplier', SupplierController::class);
    Route::get('/customer', [CustomerController::class, 'index']);

    Route::get('/cart', [CartController::class, 'getCart']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::post('/cart/update', [CartController::class, 'updateCart']);
    Route::post('/cart/remove', [CartController::class, 'removeCart']);
    Route::delete('/cart/clear', [CartController::class, 'clearCart']);
    Route::post('/checkout', [ChekoutController::class, 'checkout']);



    Route::apiResource('penjualan', TransactionController::class);
});
Route::apiResource('purchases', PembelianController::class);
