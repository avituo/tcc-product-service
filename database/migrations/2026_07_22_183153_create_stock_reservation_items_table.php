<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('stock_reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('sku');
            $table->string('product_name');
            $table->decimal('list_price', total: 12, places: 2);
            $table->decimal('discount', total: 12, places: 2);
            $table->decimal('unit_price', total: 12, places: 2);
            $table->timestamps();

            $table->unique(['stock_reservation_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservation_items');
    }
};
