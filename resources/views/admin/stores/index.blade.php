@extends('layouts.admin')

@section('title', 'Lojas | Admin')
@section('heading', 'Lojas')
@section('subtitle', 'Unidades e endereços')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600">Total: {{ $stores->total() }}</p>
        <a class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" href="{{ route('admin.stores.create') }}">
            Nova loja
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Cidade</th>
                    <th class="px-4 py-3">UF</th>
                    <th class="px-4 py-3">Ativa</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($stores as $store)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $store->name }}</p>
                            <p class="mt-1 font-mono text-xs text-slate-600">{{ $store->slug }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $store->city ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ $store->state ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($store->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Sim</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Não</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50" href="{{ route('admin.stores.show', $store) }}">Ver</a>
                                <a class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50" href="{{ route('admin.stores.edit', $store) }}">Editar</a>
                                <form action="{{ route('admin.stores.destroy', $store) }}" method="post" onsubmit="return confirm('Remover loja?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800 hover:bg-rose-100" type="submit">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-slate-600" colspan="5">Nenhuma loja cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $stores->links() }}
    </div>
@endsection
