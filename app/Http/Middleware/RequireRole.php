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
        $identity = $request->attributes->get('identity');
        $userRoles = is_array($identity) ? ($identity['roles'] ?? []) : [];
        if (array_intersect($roles, $userRoles) === []) {
            return response()->json(['code' => 'forbidden', 'message' => 'The authenticated identity lacks the required role.', 'details' => ['required_roles' => $roles]], 403);
        }

        return $next($request);
    }
}
