<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_only_lists_active_products_and_returns_money_as_strings(): void
    {
        Product::factory()->create(['name' => 'Visible', 'price' => '100.00', 'discount' => '10.50', 'is_active' => true]);
        Product::factory()->create(['name' => 'Hidden', 'is_active' => false]);

        $this->getJson('/api/v1/products')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Visible')->assertJsonPath('data.0.price', '100.00')
            ->assertJsonPath('data.0.sale_price', '89.50');
    }

    public function test_catalog_filters_and_validates_pagination(): void
    {
        Product::factory()->create(['name' => 'Blue chair', 'is_active' => true]);
        Product::factory()->create(['name' => 'Red table', 'is_active' => true]);

        $this->getJson('/api/v1/products?name=chair&per_page=1')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Blue chair');
        $this->getJson('/api/v1/products?per_page=101')->assertUnprocessable();
    }
}
