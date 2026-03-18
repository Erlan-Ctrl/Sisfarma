@extends('layouts.admin')

@section('title', 'Fornecedor | Admin')
@section('heading', 'Fornecedor')
@section('subtitle', $supplier->name)

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Nome</dt>
                <dd class="mt-1 font-semibold">{{ $supplier->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Status</dt>
                <dd class="mt-1">
                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $supplier->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                        {{ $supplier->is_active ? 'Ativo' : 'Desativado' }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">CNPJ</dt>
                <dd class="mt-1 font-mono text-xs">{{ $supplier->cnpj ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Telefone</dt>
                <dd class="mt-1">{{ $supplier->phone ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">E-mail</dt>
                <dd class="mt-1">{{ $supplier->email ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produtos vinculados</dt>
                <dd class="mt-1 font-semibold">{{ (int) ($supplier->products_count ?? 0) }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Observações</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $supplier->notes ?: '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.suppliers.edit', $supplier) }}">
                Editar
            </a>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.suppliers.index') }}">
                Voltar
            </a>
            <a class="ml-auto rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.products.index', ['supplier_id' => $supplier->getKey()]) }}">
                Ver produtos deste fornecedor
            </a>
        </div>
    </div>
@endsection

