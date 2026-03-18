@extends('layouts.admin')

@section('title', 'Editar categoria | Admin')
@section('heading', 'Editar categoria')
@section('subtitle', $category->name)

@section('content')
    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.categories.update', $category) }}" method="post">
        @csrf
        @method('PUT')

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Nome</label>
            <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="name" value="{{ old('name', $category->name) }}" required>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Slug</label>
            <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="slug" value="{{ old('slug', $category->slug) }}">
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Descrição</label>
            <textarea class="min-h-28 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="description">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Ordem</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}">
            </div>

            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                <span class="font-semibold text-slate-800">Ativa</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">
                Atualizar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.categories.index') }}">Voltar</a>
            <a class="ml-auto rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.categories.show', $category) }}">
                Ver
            </a>
        </div>
    </form>
@endsection
