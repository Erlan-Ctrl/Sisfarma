<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    public function show(Request $request)
    {
        $hasUsers = User::query()->exists();
        $inviteCode = trim((string) config('admin.registration.invite_code', ''));
        $allowInLocal = (bool) config('admin.registration.allow_in_local', true);
        $isLocal = app()->environment('local') && $allowInLocal;

        $requiresInviteCode = $hasUsers && ! $isLocal;
        $enabled = ! $requiresInviteCode || $inviteCode !== '';

        return view('admin.auth.register', [
            'hasUsers' => $hasUsers,
            'requiresInviteCode' => $requiresInviteCode,
            'enabled' => $enabled,
        ]);
    }

    public function store(Request $request)
    {
        $minuteKey = 'admin-register:m:'.$request->ip();
        $hourKey = 'admin-register:h:'.$request->ip();

        $maxPerMinute = 3;
        $maxPerHour = 10;

        if (RateLimiter::tooManyAttempts($minuteKey, $maxPerMinute) || RateLimiter::tooManyAttempts($hourKey, $maxPerHour)) {
            $seconds = max(RateLimiter::availableIn($minuteKey), RateLimiter::availableIn($hourKey));

            return back()
                ->withErrors(['email' => "Muitas tentativas de cadastro. Tente novamente em {$seconds}s."])
                ->withInput()
                ->setStatusCode(429);
        }

        // Count all attempts (valid or not) to slow down brute force and abuse.
        RateLimiter::hit($minuteKey, 60);
        RateLimiter::hit($hourKey, 3600);

        $hasUsers = User::query()->exists();
        $inviteCode = trim((string) config('admin.registration.invite_code', ''));
        $allowInLocal = (bool) config('admin.registration.allow_in_local', true);
        $isLocal = app()->environment('local') && $allowInLocal;

        $requiresInviteCode = $hasUsers && ! $isLocal;
        $enabled = ! $requiresInviteCode || $inviteCode !== '';

        if (! $enabled) {
            abort(403);
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ];

        if ($requiresInviteCode) {
            $rules['invite_code'] = ['required', 'string', 'max:80'];
        }

        $validated = $request->validate($rules);

        if ($requiresInviteCode) {
            $provided = trim((string) ($validated['invite_code'] ?? ''));
            if ($inviteCode === '' || ! hash_equals($inviteCode, $provided)) {
                return back()
                    ->withErrors(['invite_code' => 'Código de convite inválido.'])
                    ->withInput();
            }
        }

        $isFirstUser = ! $hasUsers;
        $role = $isFirstUser
            ? 'admin'
            : (string) config('admin.registration.default_role', 'atendente');

        $allowedRoles = ['admin', 'gerente', 'atendente', 'caixa'];
        if (! in_array($role, $allowedRoles, true)) {
            $role = 'atendente';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
            'is_active' => true,
            'last_login_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('admin.dashboard')
            ->with('status', $isFirstUser ? 'Administrador criado. Bem-vindo(a)!' : 'Conta criada. Bem-vindo(a)!');
    }
}
