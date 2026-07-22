<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Token not found'], 401);
        }

        try {
            $secretKey = env('JWT_SECRET');
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            $request->attributes->set('jwt_payload', $decoded);
            $request->attributes->set('user_id', $decoded->sub ?? null);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid or expired Token'], 401);
        }

        return $next($request);
    }
}
