<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservationItem extends Model
{
    protected $fillable = ['product_id', 'quantity', 'sku', 'product_name', 'list_price', 'discount', 'unit_price'];

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'list_price' => 'decimal:2', 'discount' => 'decimal:2', 'unit_price' => 'decimal:2'];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(StockReservation::class, 'stock_reservation_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
