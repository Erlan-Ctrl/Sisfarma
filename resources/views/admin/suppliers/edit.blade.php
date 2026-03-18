@extends('layouts.admin')

@section('title', 'Editar fornecedor | Admin')
@section('heading', 'Editar fornecedor')
@section('subtitle', $supplier->name)

@section('content')
    <form class="grid gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm" action="{{ route('admin.suppliers.update', $supplier) }}" method="post">
        @csrf
        @method('put')

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Nome</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="name" value="{{ old('name', $supplier->name) }}" required>
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">CNPJ (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 font-mono text-xs shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="cnpj" value="{{ old('cnpj', $supplier->cnpj) }}">
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">Telefone (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 font-mono text-xs shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
            </div>
            <div class="grid gap-2">
                <label class="text-sm font-medium text-slate-700">E-mail (opcional)</label>
                <input class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="email" name="email" value="{{ old('email', $supplier->email) }}">
            </div>
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-slate-700">Observações (opcional)</label>
            <textarea class="min-h-28 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="notes">{{ old('notes', $supplier->notes) }}</textarea>
        </div>

        <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active))>
            <span class="font-semibold text-slate-800">Ativo</span>
        </label>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex flex-wrap items-center gap-3">
                <button class="rounded-2xl bg-brand-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Salvar
                </button>
                <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.suppliers.index') }}">Voltar</a>
                <a class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.suppliers.show', $supplier) }}">
                    Ver
                </a>
            </div>

            <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="post" onsubmit="return confirm('Desativar este fornecedor?')">
                @csrf
                @method('delete')
                <button class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="submit">
                    Desativar
                </button>
            </form>
        </div>
    </form>
@endsection

