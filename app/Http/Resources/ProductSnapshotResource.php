<?php

namespace App\Http\Resources;

use App\Support\DecimalMoney;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSnapshotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'active' => $this->is_active,
            'list_price' => $this->price,
            'discount' => $this->discount,
            'sale_price' => DecimalMoney::subtract($this->price, $this->discount),
            'available_quantity' => $this->quantity,
            'version' => $this->version,
        ];
    }
}
