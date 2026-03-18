@extends('layouts.auth')

@section('title', 'Cadastro | '.config('app.name', 'Sisfarma'))

@section('content')
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-3xl bg-brand-800 font-extrabold text-white">SS</span>
            <div class="text-left">
                <p class="text-sm font-semibold tracking-tight text-slate-900">{{ config('app.name', 'Sisfarma') }}</p>
                <p class="text-xs text-slate-600">Primeiro acesso</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h1 class="text-lg font-semibold tracking-tight">Criar conta</h1>
                @if (! $hasUsers)
                    <p class="mt-1 text-sm text-slate-600">Nenhum usuário existe ainda. O primeiro cadastro será <span class="font-semibold">Administrador</span>.</p>
                @else
                    <p class="mt-1 text-sm text-slate-600">Crie seu acesso para usar o painel.</p>
                @endif
            </div>

            <div class="px-6 py-6">
                @include('partials.flash')

                @if (! $enabled)
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        O cadastro está desativado. Procure um administrador para criar seu usuário.
                    </div>

                    <div class="mt-5">
                        <a class="inline-flex h-12 w-full items-center justify-center rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.login') }}">
                            Voltar para login
                        </a>
                    </div>
                @else
                    <form action="{{ route('admin.register.submit') }}" method="post" class="grid gap-4">
                        @csrf

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">Nome</span>
                            <input
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                            >
                        </label>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">E-mail</span>
                            <input
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                type="email"
                                name="email"
                                autocomplete="email"
                                value="{{ old('email') }}"
                                required
                            >
                        </label>

                        @if ($requiresInviteCode)
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Código de convite</span>
                                <input
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 font-mono text-xs shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                    type="text"
                                    name="invite_code"
                                    value="{{ old('invite_code') }}"
                                    placeholder="Informe o código fornecido pelo administrador"
                                    required
                                >
                            </label>
                        @endif

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Senha</span>
                                <input
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                >
                                <p class="text-xs text-slate-500">Mínimo de 8 caracteres.</p>
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Confirmar senha</span>
                                <input
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                                    type="password"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    required
                                >
                                <p class="text-xs text-slate-500 opacity-0">Minimo de 8 caracteres.</p>
                            </label>
                        </div>

                        <button class="mt-2 inline-flex h-12 items-center justify-center rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                            Criar conta
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            Já tem acesso?
            <a class="font-semibold text-brand-800 hover:text-brand-900" href="{{ route('admin.login') }}">Entrar</a>
        </p>
    </div>
@endsection
