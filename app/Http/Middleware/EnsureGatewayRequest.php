<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGatewayRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('services.gateway.key');
        $providedKeys = $request->headers->all('X-Gateway-Key');

        if (! is_string($configuredKey) || $configuredKey === '' || count($providedKeys) !== 1 || ! hash_equals($configuredKey, $providedKeys[0])) {
            return response()->json(['code' => 'gateway_unauthorized', 'message' => 'Trusted gateway credentials are required.', 'details' => (object) []], 401);
        }

        return $next($request);
    }
}
