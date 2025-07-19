<?php


use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum', 'role:manager,kasir'])->group(function () {});
// Route::middleware(['auth:sanctum', 'role:kasir'])->group(function () {
//     Route::get('/user', [AuthController::class, 'user']);
//     Route::post('/logout', [AuthController::class, 'logout']);

//     Route::apiResource('products', ProductController::class);
//     Route::apiResource('category', CategoryController::class);
//     Route::apiResource('supplier', SupplierController::class);
// });
