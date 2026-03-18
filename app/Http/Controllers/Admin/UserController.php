<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'ilike', "%{$q}%")
                        ->orWhere('email', 'ilike', "%{$q}%")
                        ->orWhere('role', 'ilike', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in($this->roles())],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $user = User::create($validated);

        $audit->log(
            action: 'user.created',
            auditable: $user,
            before: null,
            after: [
                'id' => (int) $user->getKey(),
                'name' => (string) $user->name,
                'email' => (string) $user->email,
                'role' => (string) $user->role,
                'is_active' => (bool) $user->is_active,
            ],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuário cadastrado.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user, AuditLogger $audit)
    {
        $before = $user->only(['name', 'email', 'role', 'is_active']);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->getKey())],
            'role' => ['required', 'string', Rule::in($this->roles())],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        if (($validated['password'] ?? '') === '') {
            unset($validated['password']);
        }

        $user->update($validated);

        $after = $user->fresh()?->only(array_keys($before)) ?: null;

        $audit->log(
            action: 'user.updated',
            auditable: $user,
            before: $before,
            after: $after,
            meta: [
                'changed' => array_keys(array_diff_assoc((array) $after, (array) $before)),
            ],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuário atualizado.');
    }

    public function destroy(User $user, AuditLogger $audit)
    {
        if (auth()->id() === $user->getKey()) {
            return back()->with('status', 'Você não pode desativar seu próprio usuário.');
        }

        $before = $user->only(['id', 'name', 'email', 'role', 'is_active']);

        $user->forceFill(['is_active' => false])->save();

        $after = $user->fresh()?->only(['id', 'name', 'email', 'role', 'is_active']) ?: null;

        $audit->log(
            action: 'user.disabled',
            auditable: $user,
            before: $before,
            after: $after,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuário desativado.');
    }

    /**
     * @return array<int, string>
     */
    private function roles(): array
    {
        return [
            'admin',
            'gerente',
            'atendente',
            'caixa',
        ];
    }
}
