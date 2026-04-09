@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between">
        {{-- Vista Mobile --}}
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-300 bg-white/50 border border-slate-100 rounded-2xl cursor-default">
                    Anterior
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="relative inline-flex items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest text-indigo-600 bg-white border border-slate-200 rounded-2xl hover:text-indigo-500 transition-all shadow-sm active:scale-95">
                    Anterior
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="relative inline-flex items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest text-indigo-600 bg-white border border-slate-200 rounded-2xl hover:text-indigo-500 transition-all shadow-sm active:scale-95">
                    Siguiente
                </button>
            @else
                <span class="relative inline-flex items-center px-6 py-3 text-[10px] font-black uppercase tracking-widest text-slate-300 bg-white/50 border border-slate-100 rounded-2xl cursor-default">
                    Siguiente
                </span>
            @endif
        </div>

        {{-- Vista Desktop --}}
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                    Mostrando
                    <span class="font-black text-slate-900">{{ $paginator->firstItem() }}</span>
                    al
                    <span class="font-black text-slate-900">{{ $paginator->lastItem() }}</span>
                    de
                    <span class="font-black text-slate-900">{{ $paginator->total() }}</span>
                    resultados
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex items-center gap-1.5 p-1.5 bg-slate-100/50 backdrop-blur-md border border-slate-200/50 rounded-[1.2rem] shadow-inner">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="relative inline-flex items-center p-2.5 text-slate-300 cursor-default" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                            </span>
                        </span>
                    @else
                        <button wire:click="previousPage" rel="prev" class="relative inline-flex items-center p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all duration-300 group shadow-sm hover:shadow-indigo-500/10 active:scale-90" aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="relative inline-flex items-center px-3 py-2 text-[11px] font-black text-slate-400 cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="relative inline-flex items-center px-4 py-2 text-[11px] font-black text-white bg-indigo-600 rounded-xl shadow-lg shadow-indigo-200 z-10">{{ $page }}</span>
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="relative inline-flex items-center px-4 py-2 text-[11px] font-black text-slate-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all duration-300 active:scale-90" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button wire:click="nextPage" rel="next" class="relative inline-flex items-center p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-white rounded-xl transition-all duration-300 group shadow-sm hover:shadow-indigo-500/10 active:scale-90" aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="relative inline-flex items-center p-2.5 text-slate-300 cursor-default" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
