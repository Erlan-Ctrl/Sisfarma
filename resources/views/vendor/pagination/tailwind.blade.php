@if ($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3" role="navigation" aria-label="Navegacao de paginas">
        <div class="flex flex-1 items-center justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-400 shadow-sm opacity-70 cursor-not-allowed">
                    Anterior
                </span>
            @else
                <a class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    Anterior
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="inline-flex items-center rounded-2xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800" href="{{ $paginator->nextPageUrl() }}" rel="next">
                    Proxima
                </a>
            @else
                <span class="inline-flex items-center rounded-2xl bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-sm opacity-70 cursor-not-allowed">
                    Proxima
                </span>
            @endif
        </div>

        <div class="hidden flex-1 items-center justify-between gap-3 sm:flex">
            <p class="text-sm text-slate-600">
                @if ($paginator->firstItem())
                    Mostrando <span class="font-semibold text-slate-900">{{ $paginator->firstItem() }}</span>
                    a <span class="font-semibold text-slate-900">{{ $paginator->lastItem() }}</span>
                    de <span class="font-semibold text-slate-900">{{ $paginator->total() }}</span> resultados
                @else
                    <span class="font-semibold text-slate-900">{{ $paginator->count() }}</span> resultados
                @endif
            </p>

            <div class="flex items-center gap-2">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-400 shadow-sm opacity-70 cursor-not-allowed" aria-disabled="true" aria-label="Pagina anterior">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Pagina anterior">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                <div class="flex items-center gap-1 rounded-2xl border border-slate-200 bg-white p-1 shadow-sm">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-2 text-sm font-semibold text-slate-400">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl bg-brand-700 px-3 text-sm font-semibold text-white" aria-current="page">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50" href="{{ $url }}" aria-label="Ir para pagina {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                @if ($paginator->hasMorePages())
                    <a class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-700 text-white shadow-sm hover:bg-brand-800" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Proxima pagina">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-brand-700 text-white shadow-sm opacity-70 cursor-not-allowed" aria-disabled="true" aria-label="Proxima pagina">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif

