<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the deterministic thesis benchmark products.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        $products = [];

        for ($ordinal = 1; $ordinal <= BenchmarkDataset::PRODUCT_COUNT; $ordinal++) {
            $product = BenchmarkDataset::product($ordinal);
            $products[] = [
                'id' => $product['logical_id'],
                'name' => $product['name'],
                'description' => $product['description'],
                'slug' => $product['slug'],
                'image' => $product['image'],
                'sku' => $product['sku'],
                'price' => $product['price'],
                'discount' => $product['discount'],
                'quantity' => $product['quantity'],
                'is_active' => $product['is_active'],
                'version' => $product['version'],
                'deleted_at' => null,
                'created_at' => $product['created_at'],
                'updated_at' => $product['updated_at'],
            ];
        }

        foreach (array_chunk($products, 500) as $chunk) {
            DB::table('products')->insert($chunk);
        }
    }
}
