<?php

namespace App\Actions;

use App\Exceptions\ApiProblem;
use App\Models\Product;
use App\Models\StockReservation;
use Illuminate\Support\Facades\DB;

final class ChangeStockReservationStatus
{
    public function confirm(StockReservation $reservation): StockReservation
    {
        return DB::transaction(function () use ($reservation): StockReservation {
            $locked = StockReservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($locked->status === 'confirmed') {
                return $locked->load('items');
            }
            if ($locked->status !== 'pending') {
                throw new ApiProblem('reservation_not_confirmable', 'Only a pending reservation can be confirmed.', 409);
            }
            if ($locked->expires_at->isPast()) {
                throw new ApiProblem('reservation_expired', 'The reservation has expired and must be released.', 409);
            }

            $locked->update(['status' => 'confirmed', 'confirmed_at' => now()]);

            return $locked->load('items');
        }, attempts: 3);
    }

    public function release(StockReservation $reservation): StockReservation
    {
        return DB::transaction(function () use ($reservation): StockReservation {
            $locked = StockReservation::query()->lockForUpdate()->with('items')->findOrFail($reservation->id);
            if ($locked->status === 'released') {
                return $locked;
            }
            if ($locked->status === 'confirmed') {
                throw new ApiProblem('reservation_not_releasable', 'A confirmed stock deduction cannot be released.', 409);
            }

            $products = Product::query()->whereIn('id', $locked->items->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');
            foreach ($locked->items as $item) {
                $product = $products->get($item->product_id);
                $product->increment('quantity', $item->quantity, ['version' => $product->version + 1]);
            }
            $locked->update(['status' => 'released', 'released_at' => now()]);

            return $locked;
        }, attempts: 3);
    }
}
