<?php

namespace App\Console\Commands;

use Database\Seeders\BenchmarkDataset;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('experiment:dataset:validate')]
#[Description('Validate and print the deterministic thesis benchmark products')]
class ValidateBenchmarkDatasetCommand extends Command
{
    public function handle(): int
    {
        $productCount = DB::table('products')->count();
        $reservationCount = DB::table('stock_reservations')->count();
        $expectedProduct = BenchmarkDataset::product(1);
        $firstProduct = DB::table('products')->find(1);

        $this->line('database='.(string) DB::connection()->getDatabaseName());
        $this->line(sprintf('product_count=%d', $productCount));
        $this->line(sprintf('stock_reservation_count=%d', $reservationCount));
        $logicalFingerprint = BenchmarkDataset::logicalFingerprint();
        $this->line('logical_fingerprint='.$logicalFingerprint);

        if ($logicalFingerprint !== BenchmarkDataset::LOGICAL_FINGERPRINT
            || $productCount !== BenchmarkDataset::PRODUCT_COUNT
            || $reservationCount !== 0
            || $firstProduct === null
            || $firstProduct->sku !== $expectedProduct['sku']
            || (string) $firstProduct->price !== $expectedProduct['price']
            || (int) $firstProduct->quantity !== $expectedProduct['quantity']
        ) {
            $this->error('benchmark_products=INVALID');

            return self::FAILURE;
        }

        $this->info('benchmark_products=VALID');

        return self::SUCCESS;
    }
}
