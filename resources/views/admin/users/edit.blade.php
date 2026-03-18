@extends('layouts.admin')

@section('title', 'Editar usuário | Admin')
@section('heading', 'Editar usuário')
@section('subtitle', $user->name)

@section('content')
    <form class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.users.update', $user) }}" method="post">
        @csrf
        @method('put')

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nome</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">E-mail</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Perfil</label>
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="role" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $user->role) === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Status</label>
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-200" type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))>
                    <span class="font-semibold text-slate-800">Ativo</span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nova senha (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="password" name="password">
                <p class="text-xs text-slate-500">Deixe em branco para manter a senha atual.</p>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Confirmar nova senha</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="password" name="password_confirmation">
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex flex-wrap items-center gap-3">
                <button class="rounded-2xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Salvar
                </button>
                <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.users.index') }}">Voltar</a>
            </div>

            <form action="{{ route('admin.users.destroy', $user) }}" method="post" onsubmit="return confirm('Desativar este usuário?')">
                @csrf
                @method('delete')
                <button class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="submit">
                    Desativar
                </button>
            </form>
        </div>
    </form>
@endsection

