<?php

namespace App\Http\Controllers\Api\V1\Internal;

use App\Actions\ChangeStockReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StockReservationResource;
use App\Models\StockReservation;

class ConfirmStockReservationController extends Controller
{
    public function __invoke(
        StockReservation $reservation,
        ChangeStockReservationStatus $changeStatus,
    ): StockReservationResource {
        return new StockReservationResource($changeStatus->confirm($reservation));
    }
}
