<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [UserController::class, 'register']);
Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
Route::post('/resend-otp', [UserController::class, 'resendOtp']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('admin')->middleware(['auth:sanctum', 'CheckAdmin'])->group(function () {
    Route::apiResource('category',CategoryController::class);

    Route::apiResource('product',ProductController::class);
    
    Route::apiResource('profile',ProfileController::class);

    Route::get('/users', [UserController::class, 'getusers']);
    Route::post('/users/{id}/block', [UserController::class, 'blockUser']);
    Route::post('/users/{id}/unblock', [UserController::class, 'unblockUser']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{id}/edit-status', [OrderController::class, 'editStatus']);


});