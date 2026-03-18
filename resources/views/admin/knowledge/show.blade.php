@extends('layouts.admin')

@section('title', 'Entrada | Admin')
@section('heading', 'Conhecimento')
@section('subtitle', $entry->title)

@section('content')
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Entrada</p>
                <h2 class="mt-2 text-xl font-semibold tracking-tight">{{ $entry->title }}</h2>
                <p class="mt-2 text-sm text-slate-600">
                    @if ($entry->tags)
                        Tags: <span class="font-semibold text-slate-900">{{ $entry->tags }}</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" href="{{ route('admin.knowledge.edit', $entry) }}">
                    Editar
                </a>
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold shadow-sm hover:bg-slate-100" href="{{ route('admin.knowledge.index') }}">
                    Voltar
                </a>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm leading-relaxed text-slate-900">
            <div class="whitespace-pre-line">{!! nl2br(e($entry->content)) !!}</div>
        </div>

        <div class="mt-6 text-xs text-slate-500">
            Atualizado em {{ optional($entry->updated_at)->format('d/m/Y H:i') }}.
        </div>
    </div>
@endsection

