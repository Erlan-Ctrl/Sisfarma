@extends('layouts.admin')

@section('title', 'Editar oferta | Admin')
@section('heading', 'Editar oferta')
@section('subtitle', $offer->title)

@section('content')
    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.offers.update', $offer) }}" method="post">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Título</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="title" value="{{ old('title', $offer->title) }}" required>
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Slug</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="slug" value="{{ old('slug', $offer->slug) }}">
            </div>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Descrição</label>
            <textarea class="min-h-28 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="description">{{ old('description', $offer->description) }}</textarea>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Banner (URL)</label>
            <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="banner_url" value="{{ old('banner_url', $offer->banner_url) }}">
        </div>

        @php
            $startsValue = $offer->starts_at ? $offer->starts_at->format('Y-m-d\\TH:i') : '';
            $endsValue = $offer->ends_at ? $offer->ends_at->format('Y-m-d\\TH:i') : '';
        @endphp
        <div class="grid gap-4 md:grid-cols-3">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Início</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="datetime-local" name="starts_at" value="{{ old('starts_at', $startsValue) }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Fim</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="datetime-local" name="ends_at" value="{{ old('ends_at', $endsValue) }}">
            </div>
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer->is_active))>
                <span class="font-semibold text-slate-800">Ativa</span>
            </label>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Produtos</label>
            @php
                $selectedIds = old('product_ids', $offer->products->pluck('id')->all());
            @endphp
            <select class="min-h-24 rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" name="product_ids[]" multiple>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedIds, true))>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">
                Atualizar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.offers.index') }}">Voltar</a>
            <a class="ml-auto rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.offers.show', $offer) }}">
                Ver
            </a>
        </div>
    </form>
@endsection
