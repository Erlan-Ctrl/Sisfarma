@extends('layouts.site')

@section('title', config('app.name', 'Sisfarma').' | Sistema para farmácias')
@section('description', 'Sistema para farmácias com foco em velocidade no balcão: estoque por loja, compras, vendas, transferências, relatórios e auditoria.')

@section('content')
    @php
        /** @var bool $hasUsers */
        $hasUsers = isset($hasUsers) ? (bool) $hasUsers : \App\Models\User::query()->exists();

        $primaryHref = $hasUsers ? route('admin.login') : route('admin.register');
        $primaryLabel = $hasUsers ? 'Entrar no painel' : 'Criar primeiro acesso';
    @endphp

    <header class="sticky top-0 z-20 border-b border-slate-200/70 bg-white/75 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4">
            <a class="flex items-center gap-3" href="{{ route('home') }}">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-700 font-extrabold text-white shadow-sm">
                    SS
                </span>
                <div class="leading-tight">
                    <div class="text-sm font-extrabold tracking-tight text-slate-900">{{ config('app.name', 'Sisfarma') }}</div>
                    <div class="text-xs text-slate-500">Sistema para farmácias</div>
                </div>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-semibold text-slate-600 md:flex">
                <a class="hover:text-slate-900" href="#recursos">Recursos</a>
                <a class="hover:text-slate-900" href="#operacao">Operação</a>
                <a class="hover:text-slate-900" href="#seguranca">Segurança</a>
                <a class="hover:text-slate-900" href="#contato">Contato</a>
            </nav>

            <div class="flex items-center gap-2">
                <a class="hidden items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold shadow-sm hover:bg-slate-50 md:inline-flex" href="{{ route('admin.login') }}">
                    Admin
                </a>
                <a class="inline-flex items-center justify-center rounded-xl bg-brand-700 px-4 py-2 text-sm font-extrabold text-white shadow-sm hover:bg-brand-800" href="{{ $primaryHref }}">
                    {{ $primaryLabel }}
                </a>
            </div>
        </div>
    </header>

    <main data-page-root="1">
        <section class="mx-auto max-w-7xl px-4 pt-14 pb-10">
            <div class="grid items-center gap-10 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/70 px-3 py-1.5 text-xs font-bold text-slate-600 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-sun-400"></span>
                        Pronto para operação rápida no balcão
                    </div>

                    <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl">
                        Gestão de farmácia
                        <span class="bg-gradient-to-r from-brand-700 via-brand-500 to-sun-500 bg-clip-text text-transparent">rápida</span>,
                        elegante e segura.
                    </h1>

                    <p class="mt-5 max-w-xl text-lg leading-relaxed text-slate-600">
                        Estoque por loja, compras, vendas, transferências, auditoria e relatórios.
                        Tudo com foco em velocidade para fechar a venda sem o cliente esperar.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a class="inline-flex items-center justify-center rounded-2xl bg-brand-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-brand-800" href="{{ $primaryHref }}">
                            {{ $primaryLabel }}
                        </a>
                        <a class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-800 shadow-sm hover:bg-slate-50" href="#recursos">
                            Ver recursos
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-6 text-sm text-slate-600">
                        <div class="flex items-center gap-2">
                            <span class="grid h-9 w-9 place-items-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                                <x-nav-icon name="inventory" class="h-5 w-5 text-brand-700" />
                            </span>
                            Estoque por filial
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="grid h-9 w-9 place-items-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                                <x-nav-icon name="reports" class="h-5 w-5 text-brand-700" />
                            </span>
                            Relatórios e margem
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="grid h-9 w-9 place-items-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                                <x-nav-icon name="audit" class="h-5 w-5 text-brand-700" />
                            </span>
                            Auditoria de ações
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-slate-200 bg-white/85 p-5 shadow-sm backdrop-blur interactive-card">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold text-slate-900">Visão rápida</div>
                            <div class="rounded-full bg-sun-500/10 px-2 py-0.5 text-[11px] font-bold text-sun-700">Demo</div>
                        </div>

                        <div class="mt-4 grid gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-slate-800">Scanner (EAN)</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">Balcão</span>
                                </div>
                                <div class="mt-2 text-xs text-slate-500">Busca e cadastro rápido por código de barras.</div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-slate-800">Multi-loja</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">Estoque</span>
                                </div>
                                <div class="mt-2 text-xs text-slate-500">Consulte outras filiais em segundos.</div>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-bold text-slate-800">NFe (XML)</span>
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">Compras</span>
                                </div>
                                <div class="mt-2 text-xs text-slate-500">Recebimento com importação para reduzir retrabalho.</div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-gradient-to-br from-brand-700 to-brand-800 px-4 py-4 text-white shadow-sm">
                            <div class="text-sm font-extrabold">Feche a venda mais rápido</div>
                            <div class="mt-1 text-xs text-white/80">Operação rápida e telas consistentes para balcão e gestão.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="recursos" class="mx-auto max-w-7xl px-4 pt-6 pb-12">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Recursos principais</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600">Módulos pensados para o fluxo real de uma farmácia.</p>
                </div>
                <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-800 shadow-sm hover:bg-slate-50" href="{{ route('admin.login') }}">
                    Acessar painel
                </a>
            </div>

            @php
                $features = [
                    ['title' => 'SNGPC', 'desc' => 'Base para cumprir as exigências da ANVISA com rastreabilidade.', 'icon' => 'audit'],
                    ['title' => 'Financeiro', 'desc' => 'Fluxo de caixa, contas a pagar/receber e visão do negócio.', 'icon' => 'reports'],
                    ['title' => 'PBMs', 'desc' => 'Estrutura para integrar programas de benefícios e convênios.', 'icon' => 'offers'],
                    ['title' => 'Fiscal', 'desc' => 'Emissão e controle de documentos fiscais sem complicação.', 'icon' => 'purchases'],
                    ['title' => 'Estoque', 'desc' => 'Controle por loja com histórico de entradas e saídas.', 'icon' => 'inventory'],
                    ['title' => 'Relatórios', 'desc' => 'Dados confiáveis para decisão: giro, margem e ranking.', 'icon' => 'reports'],
                    ['title' => 'Online', 'desc' => 'Acompanhamento gerencial sem perder sua farmácia de vista.', 'icon' => 'dashboard'],
                    ['title' => 'Parceiros', 'desc' => 'Integrações para potencializar gestão e canais de venda.', 'icon' => 'transfers'],
                ];
            @endphp

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($features as $f)
                    <div class="rounded-3xl border border-slate-200 bg-white/85 p-5 shadow-sm backdrop-blur">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-slate-50 ring-1 ring-slate-200">
                                <x-nav-icon :name="$f['icon']" class="h-5 w-5 text-brand-700" />
                            </span>
                            <div class="min-w-0">
                                <div class="text-sm font-extrabold text-slate-900">{{ $f['title'] }}</div>
                                <div class="mt-1 text-sm text-slate-600">{{ $f['desc'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="operacao" class="mx-auto max-w-7xl px-4 pb-12">
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <div class="rounded-3xl border border-slate-200 bg-white/85 p-6 shadow-sm backdrop-blur">
                        <h3 class="text-lg font-extrabold text-slate-900">Operação de balcão</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            Consulte margem, negocie preço e encontre estoque em outras lojas rapidamente.
                        </p>
                        <div class="mt-5 grid gap-3 text-sm text-slate-700">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-8 w-8 place-items-center rounded-2xl bg-sun-500/10 text-sun-700 ring-1 ring-sun-500/20">1</span>
                                <div>
                                    <div class="font-bold">Busca inteligente</div>
                                    <div class="text-slate-600">Nome, EAN, SKU e atalhos para acelerar o atendimento.</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-8 w-8 place-items-center rounded-2xl bg-sun-500/10 text-sun-700 ring-1 ring-sun-500/20">2</span>
                                <div>
                                    <div class="font-bold">Multi-filial</div>
                                    <div class="text-slate-600">Se faltar na loja, veja outras filiais sem trocar de tela.</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 grid h-8 w-8 place-items-center rounded-2xl bg-sun-500/10 text-sun-700 ring-1 ring-sun-500/20">3</span>
                                <div>
                                    <div class="font-bold">Entrada por XML</div>
                                    <div class="text-slate-600">Importe NFe para reduzir erros e acelerar recebimento.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-white/85 to-slate-50/85 p-6 shadow-sm backdrop-blur">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900">Estoque e reposição</h3>
                                <p class="mt-2 text-sm text-slate-600">
                                    Análise automática de itens abaixo do mínimo e excesso de estoque para economizar.
                                </p>
                            </div>
                            <span class="rounded-full bg-brand-700/10 px-3 py-1 text-xs font-extrabold text-brand-800">Insights</span>
                        </div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-sm font-extrabold text-slate-900">Reposição</div>
                                <div class="mt-1 text-sm text-slate-600">Estoque mínimo e histórico para não faltar produto.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-sm font-extrabold text-slate-900">Excessos</div>
                                <div class="mt-1 text-sm text-slate-600">Identifique itens parados e reduza capital preso.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-sm font-extrabold text-slate-900">Histórico</div>
                                <div class="mt-1 text-sm text-slate-600">Entradas, saídas e ajustes com rastreabilidade.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="text-sm font-extrabold text-slate-900">Alertas</div>
                                <div class="mt-1 text-sm text-slate-600">Base para avisos de divergências e atualizações.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="seguranca" class="mx-auto max-w-7xl px-4 pb-14">
            <div class="rounded-3xl border border-slate-200 bg-white/85 p-8 shadow-sm backdrop-blur">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">Segurança e rastreabilidade</h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Controle por perfil de acesso, auditoria de ações e limites de requisições em rotas sensíveis.
                        </p>
                    </div>
                    <div class="lg:col-span-7">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                    <x-nav-icon name="users" class="h-5 w-5 text-brand-700" />
                                    Perfis e permissões
                                </div>
                                <div class="mt-2 text-sm text-slate-600">Admin, gerente, atendente e caixa com regras.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                    <x-nav-icon name="audit" class="h-5 w-5 text-brand-700" />
                                    Auditoria
                                </div>
                                <div class="mt-2 text-sm text-slate-600">Registro de ações para rastrear alterações.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                    <x-nav-icon name="reports" class="h-5 w-5 text-brand-700" />
                                    Consistência
                                </div>
                                <div class="mt-2 text-sm text-slate-600">Movimentações atômicas de estoque e histórico.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex items-center gap-2 text-sm font-extrabold text-slate-900">
                                    <x-nav-icon name="assistant" class="h-5 w-5 text-brand-700" />
                                    Assistente IA (opcional)
                                </div>
                                <div class="mt-2 text-sm text-slate-600">Base para suporte interno e busca de conhecimento.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-br from-brand-700 to-brand-800 px-6 py-5 text-white">
                    <div>
                        <div class="text-sm font-extrabold">Quer ver o painel funcionando?</div>
                        <div class="mt-1 text-sm text-white/80">Entre e navegue pelas telas com dados de teste.</div>
                    </div>
                    <a class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-extrabold text-brand-800 shadow-sm hover:bg-slate-50" href="{{ $primaryHref }}">
                        {{ $primaryLabel }}
                    </a>
                </div>
            </div>
        </section>
    </main>

    <footer id="contato" class="border-t border-slate-200 bg-white/60 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-sm font-extrabold text-slate-900">{{ config('app.name', 'Sisfarma') }}</div>
                <div class="mt-1 text-sm text-slate-600">Sistema interno para farmácias.</div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-800 shadow-sm hover:bg-slate-50" href="{{ route('admin.login') }}">Login</a>
                <a class="rounded-xl bg-brand-700 px-4 py-2 text-sm font-extrabold text-white shadow-sm hover:bg-brand-800" href="{{ $primaryHref }}">{{ $primaryLabel }}</a>
            </div>
        </div>
    </footer>
@endsection

