<?php

use App\Http\Controllers\Api\V1\Internal\ConfirmStockReservationController;
use App\Http\Controllers\Api\V1\Internal\ProductSnapshotController;
use App\Http\Controllers\Api\V1\Internal\StockReservationController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::middleware(['gateway', 'role:admin'])->group(function (): void {
        Route::post('/products', [ProductController::class, 'store']);
        Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    Route::prefix('internal')->middleware('gateway')->group(function (): void {
        Route::post('/products/snapshots', ProductSnapshotController::class);
        Route::post('/stock/reservations', [StockReservationController::class, 'store']);
        Route::get('/stock/reservations/{reservation}', [StockReservationController::class, 'show']);
        Route::post('/stock/reservations/{reservation}/confirm', ConfirmStockReservationController::class);
        Route::delete('/stock/reservations/{reservation}', [StockReservationController::class, 'destroy']);
    });
});
