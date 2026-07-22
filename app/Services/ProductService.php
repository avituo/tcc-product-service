<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * @param  array{name?: mixed}  $filters
     */
    public function getListPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Product::query();

        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }
        $query->where('is_active', true);

        return $query
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
