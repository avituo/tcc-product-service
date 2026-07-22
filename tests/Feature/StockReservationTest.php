<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gateway.key' => 'test-gateway-key']);
    }

    public function test_internal_endpoints_require_gateway_credential(): void
    {
        $this->postJson('/api/v1/internal/products/snapshots', ['product_ids' => [1]])
            ->assertUnauthorized()->assertJsonPath('code', 'gateway_unauthorized');
    }

    public function test_snapshot_and_reservation_use_discount_and_are_idempotent(): void
    {
        $product = Product::factory()->create(['price' => '25.00', 'discount' => '2.50', 'quantity' => 5, 'version' => 1]);
        $headers = ['X-Gateway-Key' => 'test-gateway-key'];
        $this->withHeaders($headers)->postJson('/api/v1/internal/products/snapshots', ['product_ids' => [$product->id, 999]])
            ->assertOk()->assertJsonPath('data.0.sale_price', '22.50')->assertJsonPath('missing_product_ids.0', 999);

        $payload = ['order_id' => 'order-1', 'expires_at' => now()->addHour()->toIso8601String(),
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'expected_version' => 1]]];
        $reservationHeaders = [...$headers, 'Idempotency-Key' => 'order-1'];
        $first = $this->withHeaders($reservationHeaders)->postJson('/api/v1/internal/stock/reservations', $payload)
            ->assertSuccessful()->assertJsonPath('data.items.0.unit_price', '22.50');
        $this->assertSame(3, $product->refresh()->quantity);
        $this->withHeaders($reservationHeaders)->postJson('/api/v1/internal/stock/reservations', $payload)
            ->assertSuccessful()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertSame(3, $product->refresh()->quantity);
        $this->withHeaders($headers)->deleteJson('/api/v1/internal/stock/reservations/'.$first->json('data.id'))
            ->assertOk()->assertJsonPath('data.status', 'released');
        $this->assertSame(5, $product->refresh()->quantity);
    }

    public function test_stock_and_version_conflicts_are_stable(): void
    {
        $product = Product::factory()->create(['quantity' => 1, 'version' => 3]);
        $headers = ['X-Gateway-Key' => 'test-gateway-key', 'Idempotency-Key' => 'order-2'];
        $payload = ['order_id' => 'order-2', 'expires_at' => now()->addHour()->toIso8601String(),
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'expected_version' => 3]]];
        $this->withHeaders($headers)->postJson('/api/v1/internal/stock/reservations', $payload)
            ->assertConflict()->assertJsonPath('code', 'insufficient_stock');

        $payload['items'][0] = ['product_id' => $product->id, 'quantity' => 1, 'expected_version' => 2];
        $this->withHeaders([...$headers, 'Idempotency-Key' => 'order-3'])->postJson('/api/v1/internal/stock/reservations', $payload)
            ->assertConflict()->assertJsonPath('code', 'product_version_conflict');
    }
}
