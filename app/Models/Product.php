<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $attributes = [
        'is_active' => true,
        'version' => 1,
    ];

    protected $fillable = [
        'name',
        'description',
        'image',
        'slug',
        'sku',
        'price',
        'discount',
        'quantity',
        'is_active',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'is_active' => 'boolean',
            'version' => 'integer',
        ];
    }
}
