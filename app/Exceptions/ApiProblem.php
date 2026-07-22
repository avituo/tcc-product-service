<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;

final class ApiProblem extends HttpResponseException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(string $code, string $message, int $status, array $details = [])
    {
        parent::__construct(response()->json([
            'code' => $code,
            'message' => $message,
            'details' => $details === [] ? (object) [] : $details,
        ], $status));
    }
}
