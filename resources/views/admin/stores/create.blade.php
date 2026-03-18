@extends('layouts.admin')

@section('title', 'Nova loja | Admin')
@section('heading', 'Nova loja')
@section('subtitle', 'Cadastrar unidade')

@section('content')
    <form class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.stores.store') }}" method="post">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nome</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="name" value="{{ old('name', $store->name) }}" required>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Slug (opcional)</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="slug" value="{{ old('slug', $store->slug) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Telefone</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="phone" value="{{ old('phone', $store->phone) }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">WhatsApp</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">E-mail</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="email" name="email" value="{{ old('email', $store->email) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">CEP</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="zip_code" value="{{ old('zip_code', $store->zip_code) }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">UF</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 font-mono text-xs uppercase outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="state" maxlength="2" value="{{ old('state', $store->state) }}">
            </div>
            <div class="grid gap-2 md:col-span-2">
                <label class="text-sm font-medium text-slate-700">Cidade</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="city" value="{{ old('city', $store->city) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-12">
            <div class="grid gap-2 md:col-span-5">
                <label class="text-sm font-medium text-slate-700">Bairro</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="district" value="{{ old('district', $store->district) }}">
            </div>
            <div class="grid gap-2 md:col-span-5">
                <label class="text-sm font-medium text-slate-700">Rua</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="street" value="{{ old('street', $store->street) }}">
            </div>
            <div class="grid gap-2 md:col-span-2">
                <label class="text-sm font-medium text-slate-700">Número</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="number" value="{{ old('number', $store->number) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-12">
            <div class="grid gap-2 md:col-span-6">
                <label class="text-sm font-medium text-slate-700">Complemento</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="complement" value="{{ old('complement', $store->complement) }}">
            </div>
            <div class="grid gap-2 md:col-span-3">
                <label class="text-sm font-medium text-slate-700">Latitude</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="latitude" value="{{ old('latitude', $store->latitude) }}">
            </div>
            <div class="grid gap-2 md:col-span-3">
                <label class="text-sm font-medium text-slate-700">Longitude</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="longitude" value="{{ old('longitude', $store->longitude) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Horário (opcional)</label>
                <input class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-slate-900 focus:ring-2 focus:ring-slate-200" type="text" name="opening_hours" value="{{ old('opening_hours', $store->opening_hours) }}" placeholder="Seg a Sex 08:00-18:00">
            </div>
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                <span class="font-semibold text-slate-800">Ativa</span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800" type="submit">
                Salvar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.stores.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
