@extends('layouts.admin')

@section('title', 'Transferência | Admin')
@section('heading', 'Transferência')
@section('subtitle', 'Detalhes da transferência')

@section('content')
    <div class="grid gap-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">ID</dt>
                    <dd class="mt-1 font-mono text-xs">#{{ $transfer->getKey() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Data</dt>
                    <dd class="mt-1">{{ optional($transfer->occurred_at)->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Origem</dt>
                    <dd class="mt-1">{{ $transfer->fromStore?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Destino</dt>
                    <dd class="mt-1">{{ $transfer->toStore?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Referência</dt>
                    <dd class="mt-1 font-mono text-xs">{{ $transfer->reference ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Responsável</dt>
                    <dd class="mt-1">{{ $transfer->user?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Itens</dt>
                    <dd class="mt-1 font-semibold">{{ (int) $transfer->items_count }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-widest text-slate-500">Observações</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-700">{{ $transfer->notes ?: '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a class="rounded-2xl bg-brand-700 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ route('admin.transfers.create') }}">
                    Nova transferência
                </a>
                <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.transfers.index') }}">
                    Voltar
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                <h2 class="text-base font-semibold tracking-tight">Itens</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-white">
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase tracking-widest text-slate-500">
                            <th class="px-5 py-3">Produto</th>
                            <th class="px-5 py-3">Quantidade</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($transfer->items as $item)
                            <tr>
                                <td class="px-5 py-4">
                                    @if ($item->product)
                                        <a class="font-semibold text-slate-900 hover:text-brand-800" href="{{ route('admin.products.show', $item->product) }}">
                                            {{ $item->product->name }}
                                        </a>
                                        <div class="mt-1 text-xs text-slate-500">
                                            @if ($item->product->ean)
                                                EAN: <span class="font-mono">{{ $item->product->ean }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-600">Produto removido</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-mono text-xs text-slate-700">{{ (int) $item->quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-6 text-slate-600" colspan="2">Nenhum item registrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

