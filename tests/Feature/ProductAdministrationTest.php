<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gateway.key' => 'test-gateway-key']);
    }

    public function test_write_requires_gateway_credential_and_admin_role(): void
    {
        $this->postJson('/api/v1/products', $this->productPayload())->assertUnauthorized()->assertJsonPath('code', 'gateway_unauthorized');
        $this->withHeaders(['X-Gateway-Key' => 'test-gateway-key', 'X-User-Roles' => 'user'])
            ->postJson('/api/v1/products', $this->productPayload())->assertForbidden()->assertJsonPath('code', 'forbidden');
    }

    public function test_admin_can_create_update_and_deactivate_a_product(): void
    {
        $headers = ['X-Gateway-Key' => 'test-gateway-key', 'X-User-Roles' => 'admin'];
        $created = $this->withHeaders($headers)->postJson('/api/v1/products', $this->productPayload())
            ->assertCreated()->assertJsonPath('data.sale_price', '90.00');

        $product = Product::query()->findOrFail($created->json('data.id'));
        $this->withHeaders($headers)->putJson("/api/v1/products/{$product->id}", [...$this->productPayload(), 'name' => 'Updated'])
            ->assertOk()->assertJsonPath('data.name', 'Updated');
        $this->withHeaders($headers)->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();
        $this->getJson("/api/v1/products/{$product->id}")->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function productPayload(): array
    {
        return ['name' => 'Product', 'description' => 'Description', 'slug' => 'product', 'image' => null,
            'price' => '100.00', 'discount' => '10.00', 'quantity' => 5, 'sku' => 'SKU-1', 'is_active' => true];
    }
}
