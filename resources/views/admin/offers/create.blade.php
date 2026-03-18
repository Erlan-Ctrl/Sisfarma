@extends('layouts.admin')

@section('title', 'Nova oferta | Admin')
@section('heading', 'Nova oferta')
@section('subtitle', 'Cadastrar oferta')

@section('content')
    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.offers.store') }}" method="post">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Título</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="title" value="{{ old('title', $offer->title) }}" required>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Slug (opcional)</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="slug" value="{{ old('slug', $offer->slug) }}">
            </div>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Descrição (opcional)</label>
            <textarea class="min-h-28 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="description">{{ old('description', $offer->description) }}</textarea>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Banner (URL opcional)</label>
            <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="banner_url" value="{{ old('banner_url', $offer->banner_url) }}" placeholder="https://...">
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Início (opcional)</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Fim (opcional)</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}">
            </div>
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                <span class="font-semibold text-slate-800">Ativa</span>
            </label>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Produtos (opcional)</label>
            <select class="min-h-24 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="product_ids[]" multiple>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(in_array($product->id, old('product_ids', []), true))>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500">Segure Ctrl/Command para selecionar várias.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">
                Salvar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.offers.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
