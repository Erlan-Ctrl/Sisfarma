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
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if ($roles !== []) {
            $role = (string) ($user->role ?? '');
            if (! in_array($role, $roles, true)) {
                abort(403);
            }
        }

        return $next($request);
    }
}
