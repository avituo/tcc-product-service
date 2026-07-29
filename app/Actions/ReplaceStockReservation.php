<?php

namespace App\Actions;

use App\Exceptions\ApiProblem;
use App\Models\Product;
use App\Models\StockReservation;
use App\Support\DecimalMoney;
use Illuminate\Support\Facades\DB;

class ReplaceStockReservation
{
    /**
     * @param  array{expires_at: string, items: array<int, array{product_id: int, quantity: int, expected_version: int}>}  $data
     */
    public function execute(StockReservation $reservation, array $data): StockReservation
    {
        return DB::transaction(function () use ($reservation, $data): StockReservation {
            $lockedReservation = StockReservation::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            if ($lockedReservation->status !== 'pending') {
                throw new ApiProblem('reservation_not_replaceable', 'Only a pending reservation can be replaced.', 409);
            }

            $requestedItems = collect($data['items'])->sortBy('product_id')->values();
            $previousQuantities = $lockedReservation->items->pluck('quantity', 'product_id');
            $productIds = $requestedItems->pluck('product_id')
                ->merge($previousQuantities->keys())
                ->unique()
                ->sort()
                ->values();
            $products = Product::withTrashed()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($requestedItems as $item) {
                $product = $products->get($item['product_id']);
                if ($product === null || $product->trashed() || ! $product->is_active) {
                    throw new ApiProblem('product_unavailable', 'A requested product is missing or inactive.', 422, ['product_id' => $item['product_id']]);
                }
                if ($product->version !== $item['expected_version']) {
                    throw new ApiProblem('product_version_conflict', 'A product changed since its snapshot was read.', 409, ['product_id' => $product->id, 'current_version' => $product->version]);
                }

                $availableQuantity = $product->quantity + (int) $previousQuantities->get($product->id, 0);
                if ($availableQuantity < $item['quantity']) {
                    throw new ApiProblem('insufficient_stock', 'There is not enough stock for a requested product.', 409, ['product_id' => $product->id, 'available_quantity' => $availableQuantity]);
                }
            }

            $requestedQuantities = $requestedItems->pluck('quantity', 'product_id');
            foreach ($productIds as $productId) {
                $product = $products->get($productId);
                $quantityDifference = (int) $previousQuantities->get($productId, 0)
                    - (int) $requestedQuantities->get($productId, 0);

                if ($quantityDifference > 0) {
                    $product->increment('quantity', $quantityDifference, ['version' => $product->version + 1]);
                } elseif ($quantityDifference < 0) {
                    $product->decrement('quantity', abs($quantityDifference), ['version' => $product->version + 1]);
                }
            }

            $lockedReservation->items()->delete();
            foreach ($requestedItems as $item) {
                $product = $products->get($item['product_id']);
                $lockedReservation->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'sku' => $product->sku,
                    'product_name' => $product->name,
                    'list_price' => $product->price,
                    'discount' => $product->discount,
                    'unit_price' => DecimalMoney::subtract($product->price, $product->discount),
                ]);
            }

            $lockedReservation->update(['expires_at' => $data['expires_at']]);

            return $lockedReservation->load('items');
        }, attempts: 3);
    }
}
