@extends('layouts.admin')

@section('title', 'Loja | Admin')
@section('heading', 'Loja')
@section('subtitle', $store->name)

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Nome</dt>
                <dd class="mt-1 font-semibold">{{ $store->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Slug</dt>
                <dd class="mt-1 font-mono text-xs">{{ $store->slug }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Cidade/UF</dt>
                <dd class="mt-1">{{ $store->city ?: '-' }} @if($store->state) / {{ $store->state }} @endif</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Ativa</dt>
                <dd class="mt-1">{{ $store->is_active ? 'Sim' : 'Não' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Endereço</dt>
                <dd class="mt-1 text-slate-700">
                    {{ $store->street ? $store->street.($store->number ? ', '.$store->number : '') : '-' }}
                    @if ($store->district)
                        <br>{{ $store->district }}
                    @endif
                    @if ($store->zip_code)
                        <br>CEP: {{ $store->zip_code }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Telefone</dt>
                <dd class="mt-1">{{ $store->phone ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">WhatsApp</dt>
                <dd class="mt-1">{{ $store->whatsapp ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">E-mail</dt>
                <dd class="mt-1">{{ $store->email ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Horário</dt>
                <dd class="mt-1">{{ $store->opening_hours ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Latitude</dt>
                <dd class="mt-1">{{ $store->latitude ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Longitude</dt>
                <dd class="mt-1">{{ $store->longitude ?: '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <a class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" href="{{ route('admin.stores.edit', $store) }}">
                Editar
            </a>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.stores.index') }}">
                Voltar
            </a>
        </div>
    </div>
@endsection
