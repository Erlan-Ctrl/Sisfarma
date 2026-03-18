@extends('layouts.admin')

@section('title', 'Painel | Admin')
@section('heading', 'Painel')
@section('subtitle', 'Visão geral')

@section('content')
    @php
        $role = auth()->user()?->role;
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if (in_array($role, ['admin', 'gerente', 'atendente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.products.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produtos</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['products'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente', 'atendente'], true))
            <a class="rounded-3xl border border-amber-200 bg-amber-50 p-5 shadow-sm hover:bg-amber-100/40" href="{{ route('admin.inventory.index', ['filter' => 'below_min']) }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-amber-800/80">Estoque baixo</p>
                <p class="mt-2 text-2xl font-semibold text-amber-900">{{ $counts['inventory_low'] }}</p>
                <p class="mt-1 text-xs text-amber-800/80">Abaixo do mínimo</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente', 'atendente'], true))
            <a class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm hover:bg-rose-100/40" href="{{ route('admin.inventory.index', ['filter' => 'zero']) }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-rose-800/80">Zerado</p>
                <p class="mt-2 text-2xl font-semibold text-rose-900">{{ $counts['inventory_zero'] }}</p>
                <p class="mt-1 text-xs text-rose-800/80">Sem estoque</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.suppliers.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Fornecedores</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['suppliers'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.purchases.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Compras</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['purchases'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente', 'atendente', 'caixa'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.sales.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Vendas</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['sales'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.stores.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Lojas</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['stores'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.categories.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Categorias</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['categories'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.offers.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Ofertas</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['offers'] }}</p>
            </a>
        @endif
        @if (in_array($role, ['admin'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm hover:bg-slate-50" href="{{ route('admin.users.index') }}">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Usuários</p>
                <p class="mt-2 text-2xl font-semibold">{{ $counts['users'] }}</p>
            </a>
        @endif
    </div>

    <div class="mt-8 grid gap-4 md:grid-cols-2">
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.inventory.movements.create') }}">
                <p class="text-sm font-semibold">Movimentar estoque</p>
                <p class="mt-1 text-sm text-slate-600">Entrada, saída e ajustes por loja.</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.purchases.create') }}">
                <p class="text-sm font-semibold">Registrar compra</p>
                <p class="mt-1 text-sm text-slate-600">Entrada de mercadorias com código de barras.</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente', 'atendente', 'caixa'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.sales.create') }}">
                <p class="text-sm font-semibold">Registrar venda</p>
                <p class="mt-1 text-sm text-slate-600">Saída e atualização automática do estoque.</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.transfers.create') }}">
                <p class="text-sm font-semibold">Transferir entre lojas</p>
                <p class="mt-1 text-sm text-slate-600">Envio e recebimento entre filiais.</p>
            </a>
        @endif

        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.reports.index') }}">
                <p class="text-sm font-semibold">Relatórios</p>
                <p class="mt-1 text-sm text-slate-600">Pendências, estoque baixo e movimentações recentes.</p>
            </a>
        @endif
        <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.scanner') }}">
            <p class="text-sm font-semibold">Scanner (EAN)</p>
            <p class="mt-1 text-sm text-slate-600">Localize ou cadastre produtos pelo código.</p>
        </a>
        @if (in_array($role, ['admin', 'gerente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.assistant') }}">
                <p class="text-sm font-semibold">Assistente IA</p>
                <p class="mt-1 text-sm text-slate-600">Ajuda contextual para a equipe.</p>
            </a>
        @endif
        @if (in_array($role, ['admin', 'gerente', 'atendente'], true))
            <a class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:bg-slate-50" href="{{ route('admin.products.create') }}">
                <p class="text-sm font-semibold">Cadastrar produto</p>
                <p class="mt-1 text-sm text-slate-600">Inclua EAN, fornecedor e categorias.</p>
            </a>
        @endif
    </div>
@endsection
