@extends('layouts.admin')

@section('title', 'Novo fornecedor | Admin')
@section('heading', 'Novo fornecedor')
@section('subtitle', 'Cadastrar fornecedor')

@section('content')
    <form class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.suppliers.store') }}" method="post">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nome</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">CNPJ (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 font-mono text-xs shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="cnpj" value="{{ old('cnpj') }}" placeholder="00.000.000/0000-00">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Telefone (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 font-mono text-xs shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="phone" value="{{ old('phone') }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">E-mail (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="email" name="email" value="{{ old('email') }}">
            </div>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Observações (opcional)</label>
            <textarea class="min-h-28 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="notes">{{ old('notes') }}</textarea>
        </div>

        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
            <span class="font-semibold text-slate-800">Ativo</span>
        </label>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button class="rounded-2xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                Salvar
            </button>
            <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.suppliers.index') }}">Cancelar</a>
        </div>
    </form>
@endsection

