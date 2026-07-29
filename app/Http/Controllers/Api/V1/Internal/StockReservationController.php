<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Actions\ChangeStockReservationStatus;
use App\Actions\ReserveStock;
use App\Exceptions\ApiProblem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReserveStockRequest;
use App\Http\Resources\Api\V1\StockReservationResource;
use App\Models\StockReservation;
use Illuminate\Http\JsonResponse;

class StockReservationController extends Controller
{
    public function store(ReserveStockRequest $request, ReserveStock $reserveStock): JsonResponse
    {
        $idempotencyKeys = $request->headers->all('Idempotency-Key');
        if (count($idempotencyKeys) !== 1 || trim($idempotencyKeys[0]) === '' || mb_strlen($idempotencyKeys[0]) > 255) {
            throw new ApiProblem('idempotency_key_required', 'A single valid Idempotency-Key header is required.', 422);
        }

        return (new StockReservationResource($reserveStock->execute($request->validated(), $idempotencyKeys[0])))
            ->response()->setStatusCode(201);
    }

    public function show(StockReservation $reservation): StockReservationResource
    {
        return new StockReservationResource($reservation->load('items'));
    }

    public function destroy(StockReservation $reservation, ChangeStockReservationStatus $changeStatus): StockReservationResource
    {
        return new StockReservationResource($changeStatus->release($reservation));
    }
}
