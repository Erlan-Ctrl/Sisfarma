@extends('layouts.admin')

@section('title', 'Oferta | Admin')
@section('heading', 'Oferta')
@section('subtitle', $offer->title)

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Título</dt>
                <dd class="mt-1 font-semibold">{{ $offer->title }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Slug</dt>
                <dd class="mt-1 font-mono text-xs">{{ $offer->slug }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Ativa</dt>
                <dd class="mt-1">{{ $offer->is_active ? 'Sim' : 'Não' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Período</dt>
                <dd class="mt-1">
                    {{ optional($offer->starts_at)->format('d/m/Y H:i') ?: '-' }}
                    até
                    {{ optional($offer->ends_at)->format('d/m/Y H:i') ?: '-' }}
                </dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Descrição</dt>
                <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $offer->description ?: '-' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Produtos</dt>
                <dd class="mt-2 flex flex-wrap gap-2">
                    @forelse ($offer->products as $product)
                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $product->name }}</span>
                    @empty
                        <span class="text-slate-600">-</span>
                    @endforelse
                </dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" href="{{ route('admin.offers.edit', $offer) }}">
                Editar
            </a>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.offers.index') }}">
                Voltar
            </a>
        </div>
    </div>
@endsection
