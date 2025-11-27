<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AdminUserController;

// 🔐 Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Products - Public (for customers to view)
Route::get('/products', [ProductController::class, 'index']);

// 🧍‍♂️ Authenticated User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => response()->json([
        'user' => $request->user()->load('roles')
    ]));

    Route::post('/create-checkout-session', [CheckoutController::class, 'createSession']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class,'stats']);
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
});

// 🛒 Seller Routes
Route::middleware(['auth:sanctum', 'role:seller'])->prefix('seller')->group(function () {
    // Sellers can manage their own products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

// 🧑 Customer Routes
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/profile', [CustomerController::class, 'profile']);
    Route::put('/profile', [CustomerController::class, 'updateProfile']);
});
