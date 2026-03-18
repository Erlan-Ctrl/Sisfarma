@extends('layouts.admin')

@section('title', 'Editar entrada | Admin')
@section('heading', 'Editar entrada')
@section('subtitle', $entry->title)

@section('content')
    <form class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" method="post" action="{{ route('admin.knowledge.update', $entry) }}">
        @csrf
        @method('PUT')

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Título</label>
            <input class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="title" value="{{ old('title', $entry->title) }}" required>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Conteúdo</label>
            <textarea class="min-h-48 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="content" required>{{ old('content', $entry->content) }}</textarea>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Tags</label>
                <input class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="tags" value="{{ old('tags', $entry->tags) }}">
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $entry->is_active))>
                <span class="font-semibold text-slate-800">Ativo</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" type="submit">
                Atualizar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.knowledge.index') }}">Voltar</a>
            <a class="ml-auto rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.knowledge.show', $entry) }}">
                Ver
            </a>
        </div>
    </form>
@endsection
