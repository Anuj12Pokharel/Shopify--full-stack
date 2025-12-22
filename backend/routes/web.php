<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\WebhookController;

// OAuth routes
Route::get('/install', [AuthController::class, 'install']);
Route::get('/auth/callback', [AuthController::class, 'callback']);

// API routes (requires authenticated shop session)
Route::prefix('api')->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::post('/sync/products', [SyncController::class, 'syncProducts']);
    Route::post('/sync/collections', [SyncController::class, 'syncCollections']);
    Route::post('/sync/orders', [SyncController::class, 'syncOrders']);
});

// Webhook routes
Route::prefix('webhooks')->group(function () {
    Route::post('/products/create', [WebhookController::class, 'productCreated']);
    Route::post('/products/update', [WebhookController::class, 'productUpdated']);
    Route::post('/products/delete', [WebhookController::class, 'productDeleted']);
});

// Root route
Route::get('/', function () {
    return response()->json([
        'app' => 'Shopify Embedded App',
        'version' => '1.0.0',
    ]);
});
