<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Sisfarma').' | Sistema para farmácias')</title>
        <meta name="description" content="@yield('description', 'Sistema para farmácias: estoque, compras, vendas, relatórios e auditoria com foco em velocidade e segurança.')">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        @php
            $hasViteAssets = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
        @endphp
        @if ($hasViteAssets)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @stack('head')
    </head>
    <body class="min-h-screen antialiased bg-gradient-to-b from-sun-50 via-slate-50 to-white text-slate-900">
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute inset-0 opacity-[0.22] app-grid"></div>
            <div class="absolute -top-24 left-1/2 h-72 w-[44rem] -translate-x-1/2 opacity-70">
                <div class="app-blob h-full w-full rounded-full bg-brand-200 blur-3xl"></div>
            </div>
            <div class="absolute -top-10 right-[-10rem] h-72 w-72 opacity-70">
                <div class="app-blob app-blob--slow h-full w-full rounded-full bg-sun-200 blur-3xl"></div>
            </div>
        </div>

        <div class="relative min-h-screen">
            @yield('content')
        </div>
    </body>
</html>

