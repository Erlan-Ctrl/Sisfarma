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
                            @auth
                                <div class="flex items-center gap-2">
                                    @php
                                        $user = auth()->user();
                                        $userName = trim((string) ($user?->name ?? ''));
                                        $parts = preg_split('/\\s+/', $userName) ?: [];
                                        $initials = '';
                                        foreach (array_slice($parts, 0, 2) as $p) {
                                            $p = trim((string) $p);
                                            if ($p === '') {
                                                continue;
                                            }
                                            $initials .= mb_strtoupper(mb_substr($p, 0, 1));
                                        }
                                        if ($initials === '') {
                                            $initials = 'U';
                                        }

                                        $roleLabel = match ((string) ($userRole ?? '')) {
                                            'admin' => 'Admin',
                                            'gerente' => 'Gerente',
                                            'atendente' => 'Atendente',
                                            'caixa' => 'Caixa',
                                            default => 'Usuário',
                                        };
                                    @endphp

                                    <div class="relative z-30" data-user-menu="1">
                                        <button
                                            class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-brand-100"
                                            type="button"
                                            data-user-menu-toggle="1"
                                            aria-haspopup="menu"
                                            aria-expanded="false"
                                            aria-controls="user-menu-panel"
                                        >
                                            <span class="grid h-7 w-7 place-items-center rounded-full bg-gradient-to-br from-brand-700 to-sun-500 text-xs font-extrabold text-white shadow-sm ring-1 ring-white/50">
                                                {{ $initials }}
                                            </span>
                                            <span class="hidden max-w-[10rem] truncate text-sm font-semibold text-slate-800 sm:inline">{{ $userName !== '' ? $userName : 'Usuário' }}</span>
                                            <span class="hidden rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 md:inline-flex">
                                                {{ $roleLabel }}
                                            </span>
                                            <svg class="h-4 w-4 text-slate-500 transition" data-user-menu-chevron="1" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 011.08 1.04l-4.24 4.5a.75.75 0 01-1.08 0l-4.24-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div
                                            id="user-menu-panel"
                                            class="absolute right-0 top-full mt-2 w-72 origin-top-right rounded-2xl border border-slate-200 bg-white p-2 shadow-lg shadow-slate-200/60 transition duration-150 ease-out opacity-0 scale-95"
                                            role="menu"
                                            aria-label="Menu do usuário"
                                            data-user-menu-panel="1"
                                            hidden
                                        >
                                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-3">
                                                <span class="grid h-10 w-10 place-items-center rounded-full bg-gradient-to-br from-brand-700 to-sun-500 text-sm font-extrabold text-white shadow-sm ring-1 ring-white/50">
                                                    {{ $initials }}
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="truncate text-sm font-semibold text-slate-900">{{ $userName !== '' ? $userName : 'Usuário' }}</div>
                                                    <div class="truncate text-xs text-slate-500">{{ (string) ($user?->email ?? '') }}</div>
                                                </div>
                                                <span class="ml-auto rounded-full bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200">
                                                    {{ $roleLabel }}
                                                </span>
                                            </div>

                                            <div class="mt-2 grid gap-1">
                                                @if (Route::has('admin.scanner'))
                                                    <a class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('admin.scanner') }}" role="menuitem">
                                                        <span>Scanner</span>
                                                        <span class="rounded-full bg-sun-500/10 px-2 py-0.5 text-[11px] font-semibold text-sun-700">EAN</span>
                                                    </a>
                                                @endif

                                                @if (Route::has('admin.assistant') && in_array($userRole, ['admin', 'gerente'], true))
                                                    <a class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('admin.assistant') }}" role="menuitem">
                                                        <span>Assistente IA</span>
                                                    </a>
                                                @endif

                                                @if (Route::has('admin.knowledge.index') && in_array($userRole, ['admin', 'gerente'], true))
                                                    <a class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ route('admin.knowledge.index') }}" role="menuitem">
                                                        <span>Conhecimento</span>
                                                    </a>
                                                @endif

                                                <div class="my-1 border-t border-slate-200"></div>

                                                <form action="{{ route('admin.logout') }}" method="post">
                                                    @csrf
                                                    <button class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50" type="submit" role="menuitem">
                                                        <span>Sair</span>
                                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M3 4.75A1.75 1.75 0 014.75 3h6.5A1.75 1.75 0 0113 4.75v1.5a.75.75 0 01-1.5 0v-1.5a.25.25 0 00-.25-.25h-6.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h6.5a.25.25 0 00.25-.25v-1.5a.75.75 0 011.5 0v1.5A1.75 1.75 0 0111.25 17h-6.5A1.75 1.75 0 013 15.25V4.75z" clip-rule="evenodd" />
                                                            <path fill-rule="evenodd" d="M13.72 10.53a.75.75 0 010-1.06l1.47-1.47H8.75a.75.75 0 010-1.5h6.44l-1.47-1.47a.75.75 0 111.06-1.06l3 3a.75.75 0 010 1.06l-3 3a.75.75 0 01-1.06 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
