@extends('layouts.admin')

@section('title', 'Categoria | Admin')
@section('heading', 'Categoria')
@section('subtitle', $category->name)

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Nome</dt>
                <dd class="mt-1 font-semibold">{{ $category->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Slug</dt>
                <dd class="mt-1 font-mono text-xs">{{ $category->slug }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Ativa</dt>
                <dd class="mt-1">{{ $category->is_active ? 'Sim' : 'Não' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produtos</dt>
                <dd class="mt-1">{{ $category->products_count }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Descrição</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $category->description ?: '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" href="{{ route('admin.categories.edit', $category) }}">
                Editar
            </a>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.categories.index') }}">
                Voltar
            </a>
        </div>
    </div>
@endsection
