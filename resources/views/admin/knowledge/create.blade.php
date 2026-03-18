@extends('layouts.admin')

@section('title', 'Nova entrada | Admin')
@section('heading', 'Nova entrada')
@section('subtitle', 'Adicionar ao conhecimento interno')

@section('content')
    <form class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" method="post" action="{{ route('admin.knowledge.store') }}">
        @csrf

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Título</label>
            <input class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="title" value="{{ old('title', $entry->title) }}" required>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Conteúdo</label>
            <textarea class="min-h-48 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="content" required>{{ old('content', $entry->content) }}</textarea>
            <p class="text-xs text-slate-500">Dica: escreva como um procedimento passo a passo.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Tags (opcional)</label>
                <input class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="tags" value="{{ old('tags', $entry->tags) }}" placeholder="ex.: cadastro, ofertas, caixa">
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                <span class="font-semibold text-slate-800">Ativo</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" type="submit">
                Salvar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.knowledge.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
