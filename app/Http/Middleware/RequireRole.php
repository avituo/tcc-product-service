<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $headers = $request->headers->all('X-User-Roles');
        if (count($headers) !== 1) {
            return response()->json(['code' => 'identity_invalid', 'message' => 'A single trusted roles header is required.', 'details' => (object) []], 401);
        }

        $userRoles = array_filter(array_map('trim', explode(',', $headers[0])));
        if (array_intersect($roles, $userRoles) === []) {
            return response()->json(['code' => 'forbidden', 'message' => 'The authenticated identity lacks the required role.', 'details' => ['required_roles' => $roles]], 403);
        }

        return $next($request);
    }
}
