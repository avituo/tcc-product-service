<?php

use App\Http\Controllers\Api\V1\Internal\ConfirmStockReservationController;
use App\Http\Controllers\Api\V1\Internal\ProductSnapshotController;
use App\Http\Controllers\Api\V1\Internal\ReplaceStockReservationController;
use App\Http\Controllers\Api\V1\Internal\StockReservationController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function (): void {
    Route::apiResource('products', ProductController::class)
        ->only(['index', 'show'])
        ->names('products');

    Route::middleware(['gateway', 'gateway.identity', 'role:admin'])->group(function (): void {
        Route::apiResource('products', ProductController::class)
            ->only(['store', 'update', 'destroy'])
            ->names('products');
    });

    Route::prefix('internal')->middleware('gateway')->group(function (): void {
        Route::post('/products/snapshots', ProductSnapshotController::class)->name('internal.products.snapshots');
        Route::post('/stock/reservations', [StockReservationController::class, 'store'])->name('internal.stock-reservations.store');
        Route::get('/stock/reservations/{reservation}', [StockReservationController::class, 'show'])->name('internal.stock-reservations.show');
        Route::put('/stock/reservations/{reservation}', ReplaceStockReservationController::class)->name('internal.stock-reservations.replace');
        Route::post('/stock/reservations/{reservation}/confirm', ConfirmStockReservationController::class)->name('internal.stock-reservations.confirm');
        Route::delete('/stock/reservations/{reservation}', [StockReservationController::class, 'destroy'])->name('internal.stock-reservations.destroy');
    });
});
