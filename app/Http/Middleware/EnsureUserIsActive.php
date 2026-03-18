<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->getAttribute('is_active') === false) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Usuário desativado.',
                ], 403);
            }

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'Usuário desativado.']);
        }

        return $next($request);
    }
}

