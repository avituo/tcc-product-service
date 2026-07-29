<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Actions\ReplaceStockReservation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ReplaceStockReservationRequest;
use App\Http\Resources\Api\V1\StockReservationResource;
use App\Models\StockReservation;

class ReplaceStockReservationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        ReplaceStockReservationRequest $request,
        StockReservation $reservation,
        ReplaceStockReservation $replaceReservation,
    ): StockReservationResource {
        return new StockReservationResource(
            $replaceReservation->execute($reservation, $request->validated()),
        );
    }
}
