@extends('layouts.admin')

@section('title', 'Novo usuário | Admin')
@section('heading', 'Novo usuário')
@section('subtitle', 'Acesso interno')

@section('content')
    <form class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.users.store') }}" method="post">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nome</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">E-mail</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="email" name="email" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Perfil</label>
                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="role" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', 'atendente') === $role)>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Status</label>
                <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                    <input type="hidden" name="is_active" value="0">
                    <input class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-200" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                    <span class="font-semibold text-slate-800">Ativo</span>
                </div>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Senha</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="password" name="password" required>
                <p class="text-xs text-slate-500">Mínimo de 8 caracteres.</p>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Confirmar senha</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="password" name="password_confirmation" required>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button class="rounded-2xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                Salvar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.users.index') }}">Cancelar</a>
        </div>
    </form>
@endsection

