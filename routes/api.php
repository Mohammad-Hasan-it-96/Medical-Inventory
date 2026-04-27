<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\V1;

// Note on namespace: the folder is Api/ but the namespace is API\ (legacy).
// ─────────────────────────────────────────────────────────────────────────────
//  Auth (public — no middleware)
// ─────────────────────────────────────────────────────────────────────────────

Route::post('register', [RegisterController::class, 'register']);
Route::post('login',    [RegisterController::class, 'login']);

// ─────────────────────────────────────────────────────────────────────────────
//  Legacy routes — apiResource omits Blade-only create/edit routes
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware('auth:api')->group(function () {
    Route::apiResource('products', ProductController::class);
});

// ─────────────────────────────────────────────────────────────────────────────
//  API v1 — Flutter app endpoints
// ─────────────────────────────────────────────────────────────────────────────

Route::prefix('v1')->middleware('auth:api')->group(function () {

    // Companies
    Route::get('companies', [V1\CompanyController::class, 'index']);

    // Products (with search / filter / pagination)
    Route::get('products', [V1\ProductController::class, 'index']);

    // Pharmacies
    Route::get('pharmacies',                        [V1\PharmacyController::class, 'index']);
    Route::get('pharmacies/{pharmacy}/statement',   [V1\StatementController::class, 'show']);

    // Stock
    Route::get('products/{product}/stock',          [V1\StockController::class, 'show']);

    // Orders
    Route::get('orders',                    [V1\OrderController::class, 'index']);
    Route::post('orders',                   [V1\OrderController::class, 'store']);
    Route::get('orders/{order}',            [V1\OrderController::class, 'show']);
    Route::post('orders/{order}/confirm',   [V1\OrderController::class, 'confirm']);
    Route::post('orders/{order}/cancel',    [V1\OrderController::class, 'cancel']);

    // Payments
    Route::post('payments', [V1\PaymentController::class, 'store']);

    // Rep dashboard
    Route::get('rep/dashboard', [V1\DashboardController::class, 'repDashboard']);

    // ── Sync (Flutter offline-first) ──────────────────────────────────────
    Route::prefix('sync')->group(function () {
        Route::get('bootstrap', [V1\SyncController::class, 'bootstrap']);
        Route::get('changes',   [V1\SyncController::class, 'changes']);
    });
});


