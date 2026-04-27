<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\V1;

// ─────────────────────────────────────────────────────────────────────────────
//  Auth (public — no middleware)
// ─────────────────────────────────────────────────────────────────────────────

Route::post('register', [RegisterController::class, 'register']);
Route::post('login',    [RegisterController::class, 'login']);

// ─────────────────────────────────────────────────────────────────────────────
//  Legacy resource routes (kept for backwards compatibility)
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware('auth:api')->group(function () {
    Route::resource('products', ProductController::class);
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
    Route::get('pharmacies/{pharmacy}/statement',   [V1\PharmacyController::class, 'statement']);

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
});


