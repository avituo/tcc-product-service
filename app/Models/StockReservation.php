<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockReservation extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['idempotency_key', 'order_id', 'request_hash', 'status', 'expires_at', 'confirmed_at', 'released_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'immutable_datetime', 'confirmed_at' => 'immutable_datetime', 'released_at' => 'immutable_datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReservationItem::class);
    }
}
