@extends('layouts.auth')

@section('title', 'Entrar | '.config('app.name', 'Sisfarma'))

@section('content')
    <div class="w-full max-w-md">
        <div class="mb-6 flex items-center justify-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-3xl bg-brand-800 font-extrabold text-white">SS</span>
            <div class="text-left">
                <p class="text-sm font-semibold tracking-tight text-slate-900">{{ config('app.name', 'Sisfarma') }}</p>
                <p class="text-xs text-slate-600">Acesso interno</p>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h1 class="text-lg font-semibold tracking-tight">Entrar</h1>
                <p class="mt-1 text-sm text-slate-600">Use seu e-mail e senha para acessar o painel.</p>
            </div>

            <div class="px-6 py-6">
                @include('partials.flash')

                <form action="{{ route('admin.login.submit') }}" method="post" class="grid gap-4">
                    @csrf

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

                    <label class="grid gap-2">
                        <span class="text-sm font-medium text-slate-700">Senha</span>
                        <input
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >
                    </label>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-200" type="checkbox" name="remember" value="1">
                        <span>Lembrar neste dispositivo</span>
                    </label>

                    <button class="mt-2 inline-flex h-12 items-center justify-center rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                        Entrar
                    </button>
                </form>

                <a class="mt-3 inline-flex h-12 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" href="{{ route('admin.register') }}">
                    Não tenho login
                </a>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            Problemas de acesso? Procure um administrador.
        </p>
    </div>
@endsection
