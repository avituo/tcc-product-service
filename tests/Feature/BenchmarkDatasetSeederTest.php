<?php

namespace Tests\Feature;

use Database\Seeders\BenchmarkDataset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BenchmarkDatasetSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_the_exact_deterministic_benchmark_products(): void
    {
        $this->seed();

        $this->assertSame(BenchmarkDataset::PRODUCT_COUNT, DB::table('products')->count());
        $this->assertSame(0, DB::table('stock_reservations')->count());
        $this->assertSame(BenchmarkDataset::LOGICAL_FINGERPRINT, BenchmarkDataset::logicalFingerprint());

        foreach ([1, 500, 1000] as $ordinal) {
            $product = BenchmarkDataset::product($ordinal);
            $this->assertDatabaseHas('products', [
                'id' => $product['logical_id'],
                'name' => $product['name'],
                'sku' => $product['sku'],
                'price' => $product['price'],
                'discount' => $product['discount'],
                'quantity' => $product['quantity'],
                'version' => $product['version'],
            ]);
        }

        $this->artisan('experiment:dataset:validate')->assertSuccessful();
    }
}
