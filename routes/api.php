<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController; // Uncomment when created
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CheckoutController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// 🔐 Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🧍‍♂️ Authenticated User Routes
Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/user', fn(Request $request) => $request->user());

    Route::get('/user', fn(Request $request) => response()->json([
        'user' => $request->user()
    ]));

    Route::post('/create-checkout-session', [CheckoutController::class, 'createSession'])->middleware('auth:sanctum');

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

Route::middleware('auth:sanctum')->get('/dashboard/stats', [DashboardController::class,'stats']);

// 🧑‍💼 Admin Routes (Protected by role:admin)
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Uncomment these once ProductController is created
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

// 🧑 Customer Routes (Protected by role:customer)
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    // Add your customer endpoints here
    Route::get('/profile', [CustomerController::class, 'profile']);
    Route::put('/profile', [CustomerController::class, 'updateProfile']);
});

// Route::middleware(['auth:sanctum', 'role:admin, customer'])->group(function () {
//     Route::get ('/dashboard', [DashboardController::class, 'index']);
// });

Route::middleware(['auth:sanctum', 'role:admin,customer'])->get('/dashboard', function(Request $request) {
    return response()->json([
        'message' => 'Welcome to your dashboard',
        'user' => $request->user()
    ]);
});
