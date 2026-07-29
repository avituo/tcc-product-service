<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProductSnapshotsRequest;
use App\Http\Resources\Api\V1\ProductSnapshotResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductSnapshotController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ProductSnapshotsRequest $request): JsonResponse
    {
        $requestedIds = collect($request->validated('product_ids'));
        $products = Product::query()->whereIn('id', $requestedIds)->get();
        $foundIds = $products->pluck('id');

        return response()->json([
            'data' => ProductSnapshotResource::collection($products)->resolve($request),
            'missing_product_ids' => $requestedIds->diff($foundIds)->values(),
        ]);
    }
}
