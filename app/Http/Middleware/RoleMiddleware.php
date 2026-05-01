<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: middleware('role:manager,supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            abort(403, 'Unauthorized — no role assigned.');
        }

        if (!in_array($user->role->slug, $roles)) {
            abort(403, 'Unauthorized — insufficient role permissions.');
        }

        return $next($request);
    }
}
