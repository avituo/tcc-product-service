<?php

namespace App\Actions;

use App\Exceptions\ApiProblem;
use App\Models\Product;
use App\Models\StockReservation;
use App\Support\DecimalMoney;
use Illuminate\Support\Facades\DB;

final class ReserveStock
{
    /**
     * @param  array{order_id: string, expires_at: string, items: array<int, array{product_id: int, quantity: int, expected_version: int}>}  $data
     */
    public function execute(array $data, string $idempotencyKey): StockReservation
    {
        $requestHash = hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($data, $idempotencyKey, $requestHash): StockReservation {
            $existing = StockReservation::query()->where('idempotency_key', $idempotencyKey)->with('items')->first();
            if ($existing !== null) {
                if (! hash_equals($existing->request_hash, $requestHash)) {
                    throw new ApiProblem('idempotency_conflict', 'The idempotency key was already used with a different request.', 409);
                }

                return $existing;
            }

            $items = collect($data['items'])->sortBy('product_id')->values();
            $products = Product::query()->whereIn('id', $items->pluck('product_id'))->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                if ($product === null || ! $product->is_active) {
                    throw new ApiProblem('product_unavailable', 'A requested product is missing or inactive.', 422, ['product_id' => $item['product_id']]);
                }
                if ($product->version !== $item['expected_version']) {
                    throw new ApiProblem('product_version_conflict', 'A product changed since its snapshot was read.', 409, ['product_id' => $product->id, 'current_version' => $product->version]);
                }
                if ($product->quantity < $item['quantity']) {
                    throw new ApiProblem('insufficient_stock', 'There is not enough stock for a requested product.', 409, ['product_id' => $product->id, 'available_quantity' => $product->quantity]);
                }
            }

            $reservation = StockReservation::query()->create([
                'idempotency_key' => $idempotencyKey,
                'order_id' => $data['order_id'],
                'request_hash' => $requestHash,
                'status' => 'pending',
                'expires_at' => $data['expires_at'],
            ]);

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $reservation->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'sku' => $product->sku,
                    'product_name' => $product->name,
                    'list_price' => $product->price,
                    'discount' => $product->discount,
                    'unit_price' => DecimalMoney::subtract($product->price, $product->discount),
                ]);
                $product->decrement('quantity', $item['quantity'], ['version' => $product->version + 1]);
            }

            return $reservation->load('items');
        }, attempts: 3);
    }
}
