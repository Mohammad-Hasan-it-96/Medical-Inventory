<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\V1\CompanyController      as V1Company;
use App\Http\Controllers\API\V1\DashboardController    as V1Dashboard;
use App\Http\Controllers\API\V1\OrderController        as V1Order;
use App\Http\Controllers\API\V1\PaymentController      as V1Payment;
use App\Http\Controllers\API\V1\PharmacyController     as V1Pharmacy;
use App\Http\Controllers\API\V1\ProductController      as V1Product;
use App\Http\Controllers\API\V1\StatementController    as V1Statement;
use App\Http\Controllers\API\V1\StockController        as V1Stock;
use App\Http\Controllers\API\V1\SyncController         as V1Sync;

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
    Route::get('companies', [V1Company::class, 'index']);

    // Products (search / filter / pagination)
    Route::get('products',                      [V1Product::class, 'index']);
    Route::get('products/{product}/stock',      [V1Stock::class,   'show']);

    // Pharmacies
    Route::get('pharmacies',                    [V1Pharmacy::class,  'index']);
    Route::get('pharmacies/{pharmacy}/statement',[V1Statement::class, 'pharmacy']);

    // Orders
    Route::get('orders',                        [V1Order::class, 'index']);
    Route::post('orders',                       [V1Order::class, 'store']);
    Route::get('orders/{order}',                [V1Order::class, 'show']);
    Route::post('orders/{order}/confirm',       [V1Order::class, 'confirm']);
    Route::post('orders/{order}/cancel',        [V1Order::class, 'cancel']);

    // Payments
    Route::get('payments',  [V1Payment::class, 'index']);
    Route::post('payments', [V1Payment::class, 'store']);

    // Rep dashboard
    Route::get('rep/dashboard', [V1Dashboard::class, 'repDashboard']);

    // Sync (Flutter offline-first)
    Route::prefix('sync')->group(function () {
        Route::get('bootstrap', [V1Sync::class, 'bootstrap']);
        Route::get('changes',   [V1Sync::class, 'changes']);
    });
});
