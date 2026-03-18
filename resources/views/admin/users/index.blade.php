@extends('layouts.admin')

@section('title', 'Usuários | Admin')
@section('heading', 'Usuários')
@section('subtitle', 'Acesso interno e permissões')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form class="flex flex-wrap items-center gap-2" method="get" action="{{ route('admin.users.index') }}">
                <input class="h-11 w-64 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" name="q" value="{{ $q }}" placeholder="Buscar por nome, e-mail, perfil">
                <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Buscar
                </button>
                @if (($q ?? '') !== '')
                    <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.users.index') }}">
                        Limpar
                    </a>
                @endif
            </form>

            <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.users.create') }}">
                Novo usuário
            </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-5 py-3">ID</th>
                            <th class="px-5 py-3">Nome</th>
                            <th class="px-5 py-3">E-mail</th>
                            <th class="px-5 py-3">Perfil</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Último acesso</th>
                            <th class="px-5 py-3">Criado em</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($users as $user)
                            <tr class="{{ $user->is_active ? '' : 'bg-slate-50/60' }}">
                                <td class="px-5 py-4 font-mono text-xs">{{ $user->id }}</td>
                                <td class="px-5 py-4 font-semibold">{{ $user->name }}</td>
                                <td class="px-5 py-4">{{ $user->email }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($user->role ?? 'atendente') }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $user->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                        {{ $user->is_active ? 'Ativo' : 'Desativado' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-xs text-slate-600">{{ optional($user->last_login_at)->format('d/m/Y H:i') ?: '-' }}</td>
                                <td class="px-5 py-4 text-xs text-slate-600">{{ optional($user->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.users.edit', $user) }}">
                                            Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="8">Nenhum usuário encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
@endsection
