<?php

use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\CartController;
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
Route::post('/resend-otp', [UserController::class, 'resendOtp'])->middleware('throttle:otp');
Route::post('/login', [UserController::class, 'login'])->middleware('throttle:login');
Route::post('/forgetpassword', [UserController::class, 'forgetpassword']);
Route::post('/resetpassword', [UserController::class, 'resetpassword']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');



Route::prefix('admin')->middleware(['auth:sanctum', 'CheckAdmin','throttle:otp'])->group(function () {
    Route::apiResource('category',CategoryController::class);

    Route::apiResource('product',ProductController::class);
    
    Route::apiResource('profile',ProfileController::class);

    Route::get('/users', [UserController::class, 'getusers']);
    Route::post('/users/{id}/block', [UserController::class, 'blockUser']);
    Route::post('/users/{id}/unblock', [UserController::class, 'unblockUser']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/{id}/edit-status', [OrderController::class, 'editStatus']);


});

Route::middleware(['auth:sanctum','throttle:otp'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});



Route::prefix('user')->middleware(['auth:sanctum', 'CheckUser','throttle:otp'])->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile', [ProfileController::class, 'store']);

    
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/items', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/items', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    Route::get('/myorders', [OrderController::class, 'myorders']);
    Route::post('/orders', [OrderController::class, 'store']); 
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);


});



Route::prefix(['user','throttle:otp'])->group(function () {
    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
});

