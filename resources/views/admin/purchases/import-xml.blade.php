@extends('layouts.admin')

@section('title', 'Importar XML | Admin')
@section('heading', 'Importar XML')
@section('subtitle', 'Entrada de mercadorias via NF-e')

@section('content')
    <div class="grid gap-6 lg:grid-cols-12">
        <section class="lg:col-span-7">
            <form class="grid gap-6" action="{{ route('admin.purchases.import_xml.store') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <h2 class="text-base font-semibold tracking-tight">Arquivo</h2>
                        <p class="mt-1 text-sm text-slate-600">Envie o XML da NF-e para criar a compra automaticamente.</p>
                    </div>

                    <div class="grid gap-4 px-6 py-6">
                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Loja</span>
                                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="store_id" required>
                                    @foreach ($stores as $s)
                                        <option value="{{ $s->getKey() }}" @selected((int) old('store_id') === (int) $s->getKey())>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-slate-700">Fornecedor (opcional)</span>
                                <select class="h-12 rounded-2xl border border-slate-200 bg-white px-3 text-sm shadow-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-100" name="supplier_id">
                                    <option value="">Detectar pelo XML</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->getKey() }}" @selected((int) old('supplier_id') === (int) $sup->getKey())>{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-xs text-slate-500">Se o XML não tiver CNPJ do emitente, você pode selecionar manualmente.</span>
                            </label>
                        </div>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-slate-700">XML da NF-e</span>
                            <input class="block w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-brand-700 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-800" type="file" name="xml" accept=".xml,text/xml,application/xml" required>
                            <span class="text-xs text-slate-500">Tamanho máximo: 5MB.</span>
                        </label>

                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <input class="mt-1 h-4 w-4 rounded border-slate-300 text-brand-700 focus:ring-brand-200" type="checkbox" name="create_products" value="1" @checked(old('create_products', '1') === '1')>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Criar produtos automaticamente</p>
                                <p class="mt-1 text-xs text-slate-600">Se um item do XML não existir no catálogo, ele será cadastrado com nome + EAN (quando disponível).</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-3">
                    <a class="text-sm font-semibold text-slate-700 hover:text-slate-900" href="{{ route('admin.purchases.index') }}">
                        Voltar
                    </a>
                    <button class="rounded-2xl bg-brand-700 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" type="submit">
                        Importar XML
                    </button>
                </div>
            </form>
        </section>

        <aside class="lg:col-span-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight">Como funciona</h2>
                <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-slate-700">
                    <li>O sistema cria uma <span class="font-semibold">Compra</span> com os itens do XML.</li>
                    <li>Para cada item, registra a entrada no estoque e atualiza o <span class="font-semibold">último custo</span> da loja.</li>
                    <li>Itens com quantidade fracionada ainda não são suportados.</li>
                    <li>Você pode revisar a compra depois e ajustar observações/itens manualmente (futuro).</li>
                </ul>
            </div>
        </aside>
    </div>
@endsection

