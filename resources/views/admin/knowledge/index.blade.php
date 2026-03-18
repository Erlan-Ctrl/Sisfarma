@extends('layouts.admin')

@section('title', 'Conhecimento | Admin')
@section('heading', 'Conhecimento')
@section('subtitle', 'Procedimentos, FAQ e padrões internos')

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <form class="w-full md:w-auto" method="get" action="{{ route('admin.knowledge.index') }}">
            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                <input class="w-full bg-transparent text-sm outline-none placeholder:text-slate-400 md:w-80" type="search" name="q" placeholder="Buscar conhecimento..." value="{{ $q }}">
                <button class="rounded-xl bg-brand-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-800" type="submit">Buscar</button>
            </div>
        </form>

        <a class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800" href="{{ route('admin.knowledge.create') }}">
            Nova entrada
        </a>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500">
                <tr>
                    <th class="px-4 py-3">Título</th>
                    <th class="px-4 py-3">Tags</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Atualizado</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($entries as $entry)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-semibold">{{ $entry->title }}</p>
                            <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $entry->content }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $entry->tags ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($entry->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">Ativo</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Inativo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ optional($entry->updated_at)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.knowledge.show', $entry) }}">Ver</a>
                                <a class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm hover:bg-slate-50" href="{{ route('admin.knowledge.edit', $entry) }}">Editar</a>
                                <form action="{{ route('admin.knowledge.destroy', $entry) }}" method="post" onsubmit="return confirm('Remover entrada?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 shadow-sm hover:bg-rose-100" type="submit">
                                        Remover
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-slate-600" colspan="5">Nenhuma entrada cadastrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $entries->links() }}
    </div>
@endsection
