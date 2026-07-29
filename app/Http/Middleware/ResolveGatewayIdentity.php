<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGatewayIdentity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = trim((string) $request->header('X-User-Id'));
        $userEmail = trim((string) $request->header('X-User-Email'));
        $userName = trim((string) $request->header('X-User-Name'));
        $roles = array_values(array_filter(array_map(
            static fn (string $role): string => mb_strtolower(trim($role)),
            explode(',', (string) $request->header('X-User-Roles')),
        )));

        if ($userId === '' || mb_strlen($userId) > 255 || mb_strlen($userEmail) > 255 || mb_strlen($userName) > 255) {
            return response()->json([
                'code' => 'identity_invalid',
                'message' => 'A valid gateway identity is required.',
                'details' => (object) [],
            ], 401);
        }

        $request->attributes->set('identity', [
            'id' => $userId,
            'email' => $userEmail !== '' ? $userEmail : null,
            'name' => $userName !== '' ? $userName : null,
            'roles' => $roles,
        ]);

        return $next($request);
    }
}
