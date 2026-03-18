<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show()
    {
        if (! User::query()->exists()) {
            return redirect()->route('admin.register');
        }

        return view('admin.auth.login');
    }

    private function loginLimiterKey(Request $request): string
    {
        $email = Str::lower(trim((string) $request->input('email', '')));

        // Avoid unbounded keys (and leaking email in cache keys).
        return 'admin-login:'.sha1($email.'|'.$request->ip());
    }

    public function login(Request $request, AuditLogger $audit)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $emailKey = $this->loginLimiterKey($request);
        $ipKey = 'admin-login:ip:'.$request->ip();

        $maxPerEmail = 5;
        $maxPerIp = 50;

        if (RateLimiter::tooManyAttempts($emailKey, $maxPerEmail) || RateLimiter::tooManyAttempts($ipKey, $maxPerIp)) {
            $seconds = max(RateLimiter::availableIn($emailKey), RateLimiter::availableIn($ipKey));

            throw ValidationException::withMessages([
                'email' => ["Muitas tentativas. Tente novamente em {$seconds}s."],
            ])->status(429);
        }

        if (! Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            RateLimiter::hit($emailKey, 60);
            RateLimiter::hit($ipKey, 60);

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        RateLimiter::clear($emailKey);

        $request->session()->regenerate();

        $user = $request->user();
        if ($user && $user->getAttribute('is_active') === false) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => ['Usuário desativado.'],
            ]);
        }

        if ($user) {
            $user->forceFill(['last_login_at' => now()])->save();

            $audit->log(
                action: 'auth.login',
                auditable: $user,
                before: null,
                after: [
                    'user_id' => (int) $user->getKey(),
                    'email' => (string) $user->email,
                    'role' => (string) $user->role,
                ],
            );
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request, AuditLogger $audit)
    {
        $user = $request->user();
        if ($user) {
            $audit->log(
                action: 'auth.logout',
                auditable: $user,
                before: null,
                after: [
                    'user_id' => (int) $user->getKey(),
                    'email' => (string) $user->email,
                    'role' => (string) $user->role,
                ],
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
