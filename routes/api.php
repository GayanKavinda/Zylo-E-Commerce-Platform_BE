<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SellerController;

// 🔐 Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Products - Public (for customers to view)
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/categories', [ProductController::class, 'categories']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/{id}/related', [ProductController::class, 'related']);

// Reviews - Public (anyone can view)
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);

// 🧍‍♂️ Authenticated User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => response()->json([
        'user' => $request->user()->load('roles')
    ]));

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class,'stats']);

    // Cart Management
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    // Order Management
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/my-reviews', [ReviewController::class, 'userReviews']);

    // Legacy checkout (keeping for compatibility)
    Route::post('/create-checkout-session', [CheckoutController::class, 'createSession']);
});

// 🔥 Admin & SuperAdmin Routes
Route::middleware(['auth:sanctum', 'role:superadmin,admin'])->prefix('admin')->group(function () {
    // User Management
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    Route::post('/users/{id}/change-role', [AdminUserController::class, 'changeRole']);
    Route::get('/roles', [AdminUserController::class, 'getRoles']);
    
    // Product Management
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Order Management
    Route::get('/orders', [OrderController::class, 'adminIndex']);
    Route::get('/orders/statistics', [OrderController::class, 'statistics']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::put('/orders/{id}/payment-status', [OrderController::class, 'updatePaymentStatus']);
});

// 🛒 Seller Routes
Route::middleware(['auth:sanctum', 'role:seller'])->prefix('seller')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SellerController::class, 'dashboard']);
    Route::get('/analytics', [SellerController::class, 'analytics']);
    Route::get('/inventory-alerts', [SellerController::class, 'inventoryAlerts']);

    // Product Management
    Route::get('/products', [SellerController::class, 'products']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Order Fulfillment
    Route::get('/orders', [SellerController::class, 'orders']);
    Route::put('/orders/{id}/fulfillment-status', [SellerController::class, 'updateFulfillmentStatus']);
});

// 🧑 Customer Routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/profile', [CustomerController::class, 'profile']);
    Route::put('/profile', [CustomerController::class, 'updateProfile']);
});
