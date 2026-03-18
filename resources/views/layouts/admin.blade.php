<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Admin | '.config('app.name', 'Sisfarma'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        @php
            $hasViteAssets = file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json'));
        @endphp
        @if ($hasViteAssets)
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
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

        @php
            $userRole = auth()->user()?->role;

            $navItems = [
                [
                    'label' => 'Painel',
                    'route' => 'admin.dashboard',
                    'active' => request()->routeIs('admin.dashboard'),
                ],
                [
                    'label' => 'Relatórios',
                    'route' => 'admin.reports.index',
                    'active' => request()->routeIs('admin.reports.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Produtos',
                    'route' => 'admin.products.index',
                    'active' => request()->routeIs('admin.products.*'),
                    'roles' => ['admin', 'gerente', 'atendente'],
                ],
                [
                    'label' => 'Estoque',
                    'route' => 'admin.inventory.index',
                    'active' => request()->routeIs('admin.inventory.*'),
                    'roles' => ['admin', 'gerente', 'atendente'],
                ],
                [
                    'label' => 'Compras',
                    'route' => 'admin.purchases.index',
                    'active' => request()->routeIs('admin.purchases.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Vendas',
                    'route' => 'admin.sales.index',
                    'active' => request()->routeIs('admin.sales.*'),
                    'roles' => ['admin', 'gerente', 'atendente', 'caixa'],
                ],
                [
                    'label' => 'Transferências',
                    'route' => 'admin.transfers.index',
                    'active' => request()->routeIs('admin.transfers.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Fornecedores',
                    'route' => 'admin.suppliers.index',
                    'active' => request()->routeIs('admin.suppliers.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Categorias',
                    'route' => 'admin.categories.index',
                    'active' => request()->routeIs('admin.categories.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Ofertas',
                    'route' => 'admin.offers.index',
                    'active' => request()->routeIs('admin.offers.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Lojas',
                    'route' => 'admin.stores.index',
                    'active' => request()->routeIs('admin.stores.*'),
                    'roles' => ['admin', 'gerente'],
                ],
                [
                    'label' => 'Usuários',
                    'route' => 'admin.users.index',
                    'active' => request()->routeIs('admin.users.*'),
                    'roles' => ['admin'],
                ],
                [
                    'label' => 'Auditoria',
                    'route' => 'admin.audit.index',
                    'active' => request()->routeIs('admin.audit.*'),
                    'roles' => ['admin', 'gerente'],
                ],
            ];

            $navItems = array_values(array_filter($navItems, function (array $item) use ($userRole): bool {
                $roles = $item['roles'] ?? null;
                if (! is_array($roles) || $roles === []) {
                    return true;
                }

                return $userRole && in_array($userRole, $roles, true);
            }));
        @endphp

        <div class="relative flex min-h-screen">
            <aside class="hidden w-72 flex-col border-r border-brand-800/60 bg-gradient-to-b from-brand-900 to-brand-800 text-brand-50 md:flex">
                <div class="p-6">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-sun-500 font-extrabold text-brand-900">SS</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold tracking-tight">{{ config('app.name', 'Sisfarma') }}</p>
                            <p class="truncate text-xs text-brand-100/80">Painel interno</p>
                        </div>
                    </a>
                </div>

                <nav class="px-3 pb-6 text-sm">
                    @foreach ($navItems as $item)
                        <a
                            class="mb-1 flex items-center justify-between rounded-xl px-3 py-2.5 transition duration-200 ease-out motion-safe:hover:translate-x-0.5
                                {{ $item['active'] ? 'bg-white/10 text-white' : 'text-brand-100/90 hover:bg-white/10 hover:text-white' }}"
                            href="{{ route($item['route']) }}"
                        >
                            <span class="font-semibold">{{ $item['label'] }}</span>
                            @if ($item['active'])
                                <span class="h-2 w-2 rounded-full bg-sun-400"></span>
                            @endif
                        </a>
                    @endforeach

                    @if (Route::has('admin.scanner'))
                        <div class="mt-4 border-t border-white/10 pt-4">
                            <a class="mb-1 flex items-center justify-between rounded-xl px-3 py-2.5 text-brand-100/90 hover:bg-white/10 hover:text-white {{ request()->routeIs('admin.scanner*') ? 'bg-white/10 text-white' : '' }}" href="{{ route('admin.scanner') }}">
                                <span class="font-semibold">Scanner</span>
                                <span class="rounded-full bg-sun-500/20 px-2 py-0.5 text-[11px] font-semibold text-sun-100">EAN</span>
                            </a>
                        </div>
                    @endif

                    @if (Route::has('admin.assistant') && in_array($userRole, ['admin', 'gerente'], true))
                        <a class="mb-1 flex items-center justify-between rounded-xl px-3 py-2.5 text-brand-100/90 hover:bg-white/10 hover:text-white {{ request()->routeIs('admin.assistant*') ? 'bg-white/10 text-white' : '' }}" href="{{ route('admin.assistant') }}">
                            <span class="font-semibold">Assistente IA</span>
                        </a>
                    @endif

                    @if (Route::has('admin.knowledge.index') && in_array($userRole, ['admin', 'gerente'], true))
                        <a class="mb-1 flex items-center justify-between rounded-xl px-3 py-2.5 text-brand-100/90 hover:bg-white/10 hover:text-white {{ request()->routeIs('admin.knowledge.*') ? 'bg-white/10 text-white' : '' }}" href="{{ route('admin.knowledge.index') }}">
                            <span class="font-semibold">Conhecimento</span>
                        </a>
                    @endif
                </nav>

                <div class="mt-auto p-6 text-xs text-brand-100/80">
                    <p class="font-semibold text-brand-50">Dica</p>
                    <p class="mt-1">Use o Scanner para cadastrar/achar produtos rapidamente.</p>
                </div>
            </aside>

            <div class="flex flex-1 flex-col">
                <header class="sticky top-0 z-10 border-b border-slate-200 bg-white/80 backdrop-blur">
                    <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-4">
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <div class="md:hidden">
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                                    <span class="grid h-9 w-9 place-items-center rounded-2xl bg-brand-700 font-extrabold text-white">SS</span>
                                    <span class="text-sm font-semibold tracking-tight">{{ config('app.name', 'Sisfarma') }}</span>
                                </a>
                            </div>
                            <div class="hidden md:block">
                                <p class="truncate text-xs font-semibold uppercase tracking-widest text-slate-500">@yield('subtitle', 'Painel administrativo')</p>
                                <h1 class="truncate text-xl font-semibold tracking-tight">@yield('heading', 'Admin')</h1>
                            </div>
                        </div>

                        @if (in_array($userRole, ['admin', 'gerente', 'atendente'], true))
                            <form
                                action="{{ route('admin.products.index') }}"
                                method="get"
                                class="w-full md:w-auto"
                                data-product-autocomplete="1"
                                data-api-url="{{ route('admin.api.products.search') }}"
                                data-min-chars="2"
                                data-limit="8"
                            >
                                <div class="relative z-20 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm transition duration-200 ease-out focus-within:border-brand-300 focus-within:ring-2 focus-within:ring-brand-100" data-autocomplete-wrapper="1">
                                    <input
                                        class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400 md:w-80"
                                        type="search"
                                        name="q"
                                        placeholder="Buscar produto (nome, EAN, SKU)..."
                                        value="{{ request('q') }}"
                                        spellcheck="false"
                                        autocapitalize="off"
                                        autocomplete="off"
                                    >
                                    <button class="rounded-xl bg-brand-700 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                                        Buscar
                                    </button>
                                </div>
                            </form>
                        @endif

                        <div class="flex items-center gap-2">
                            @if (Route::has('admin.scanner'))
                                <a class="hidden h-10 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-50 md:inline-flex items-center justify-center" href="{{ route('admin.scanner') }}">
                                    Scanner
                                </a>
                            @endif

                            @auth
                                <div class="hidden items-center gap-2 md:flex">
                                    <span class="inline-flex h-10 items-center text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                                    <form action="{{ route('admin.logout') }}" method="post">
                                        @csrf
                                        <button class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-50" type="submit">
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            @endauth
                        </div>
                    </div>

                    <div class="mx-auto max-w-7xl px-4 pb-4 md:hidden">
                        <div class="flex gap-2 overflow-x-auto pb-1 text-sm">
                            @foreach ($navItems as $item)
                                <a class="shrink-0 rounded-xl border border-slate-200 bg-white px-3 py-2 font-semibold shadow-sm hover:bg-slate-50 {{ $item['active'] ? 'border-brand-300 text-brand-800' : '' }}" href="{{ route($item['route']) }}">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8" data-page-root="1">
                    @include('partials.flash')
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
