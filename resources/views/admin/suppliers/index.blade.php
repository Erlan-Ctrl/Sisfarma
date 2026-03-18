@extends('layouts.admin')

@section('title', 'Fornecedores | Admin')
@section('heading', 'Fornecedores')
@section('subtitle', 'Cadastro e relacionamento')

@section('content')
    <div class="grid gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form class="flex flex-wrap items-center gap-2" method="get" action="{{ route('admin.suppliers.index') }}">
                <input class="h-11 w-72 rounded-2xl border border-slate-200 bg-white px-4 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" type="search" name="q" value="{{ $q }}" placeholder="Buscar por nome, CNPJ, e-mail">
                <label class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm shadow-sm">
                    <input class="h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-200" type="checkbox" name="active" value="1" @checked($onlyActive)>
                    <span class="font-semibold text-slate-700">Somente ativos</span>
                </label>
                <button class="h-11 rounded-2xl bg-brand-700 px-4 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                    Buscar
                </button>
                @if (($q ?? '') !== '' || $onlyActive)
                    <a class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold shadow-sm hover:bg-slate-100 grid place-items-center" href="{{ route('admin.suppliers.index') }}">
                        Limpar
                    </a>
                @endif
            </form>

            <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.suppliers.create') }}">
                Novo fornecedor
            </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-widest text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nome</th>
                            <th class="px-5 py-3">CNPJ</th>
                            <th class="px-5 py-3">E-mail</th>
                            <th class="px-5 py-3">Produtos</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($suppliers as $supplier)
                            <tr class="{{ $supplier->is_active ? '' : 'bg-slate-50/60' }}">
                                <td class="px-5 py-4">
                                    <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.suppliers.show', $supplier) }}">
                                        {{ $supplier->name }}
                                    </a>
                                    @if ($supplier->phone)
                                        <div class="mt-1 text-xs text-slate-500">Tel.: <span class="font-mono">{{ $supplier->phone }}</span></div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ $supplier->cnpj ?: '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $supplier->email ?: '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ (int) ($supplier->products_count ?? 0) }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $supplier->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-rose-200 bg-rose-50 text-rose-800' }}">
                                        {{ $supplier->is_active ? 'Ativo' : 'Desativado' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.suppliers.edit', $supplier) }}">
                                            Editar
                                        </a>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="post" onsubmit="return confirm('Desativar este fornecedor?')">
                                            @csrf
                                            @method('delete')
                                            <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="submit">
                                                Desativar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="6">Nenhum fornecedor encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-white px-5 py-4">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
@endsection

