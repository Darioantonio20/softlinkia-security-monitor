<?php

use Livewire\Volt\Component;
use App\Models\AuditLog;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';

    public function with(): array
    {
        $query = AuditLog::query()->with('user');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('action', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($qu) {
                      $qu->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return [
            'logs' => $query->latest()->paginate(20),
        ];
    }
}; ?>

<div>
    <!-- Search Section -->
    <div class="mb-12">
        <label for="search" class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mb-4 ml-1">
            Herramienta de Búsqueda
        </label>
        <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center">
            <div class="relative max-w-2xl flex-1 group group-search w-full">
                <!-- Search Icon -->
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                
                <!-- Input -->
                <input wire:model.live="search" type="text" id="search"
                       placeholder="Escriba el nombre del operador, acción o descripción..." 
                       class="w-full pl-14 pr-12 py-4 bg-white border border-slate-200 rounded-3xl shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-sm font-bold text-slate-800 placeholder-slate-400">
                
                <!-- Loading Spinner (Livewire) -->
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-4 flex items-center pr-2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('reports.audit.pdf') }}" class="inline-flex items-center px-10 py-4 bg-slate-900 text-white rounded-[1.5rem] font-black text-[10px] uppercase tracking-[0.2em] shadow-lg hover:-translate-y-1 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                <a href="{{ route('reports.audit.csv') }}" class="inline-flex items-center px-10 py-4 bg-white border border-slate-200 text-slate-700 rounded-[1.5rem] font-black text-[10px] uppercase tracking-[0.2em] shadow-sm hover:-translate-y-1 transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    CSV
                </a>
            </div>
        </div>
        <p class="mt-4 text-[10px] font-bold text-slate-400 ml-1">
            <span class="text-indigo-600">Protocolo SOC:</span> Se exportarán todos los registros históricos de la bitácora técnica.
        </p>
    </div>

    <!-- Audit Table -->
    <div class="glass overflow-hidden border-white/40 rounded-[2.5rem] shadow-2xl premium-card">
        <!-- Desktop Table -->
        <div class="hidden md:block relative group/table" 
             x-data="{ 
                scrolledLeft: true, 
                scrolledRight: false,
                checkScroll() {
                    let el = this.$refs.scrollContainer;
                    this.scrolledLeft = el.scrollLeft <= 5;
                    this.scrolledRight = el.scrollLeft + el.clientWidth >= el.scrollWidth - 5;
                },
                scroll(dir) {
                    this.$refs.scrollContainer.scrollBy({ left: dir * 300, behavior: 'smooth' });
                }
             }" 
             x-init="setTimeout(() => checkScroll(), 100)">
            
            <!-- Directional Controls -->
            <button @click="scroll(-1)" x-show="!scrolledLeft" x-transition 
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-[0_10px_30px_-5px_rgba(79,70,229,0.5)] border border-indigo-400 hover:bg-indigo-700 hover:scale-110 transition-all duration-300 group">
                <svg class="w-6 h-6 group-active:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
            </button>
            
            <button @click="scroll(1)" x-show="!scrolledRight" x-transition 
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-[0_10px_30px_-5px_rgba(79,70,229,0.5)] border border-indigo-400 hover:bg-indigo-700 hover:scale-110 transition-all duration-300 group">
                <svg class="w-6 h-6 group-active:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="overflow-x-auto" x-ref="scrollContainer" @scroll.throttle.50ms="checkScroll()">
                <table class="min-w-full divide-y divide-slate-100/50">
                <thead class="bg-slate-50/50 backdrop-blur-md">
                    <tr>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Operador</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Registro de Acción</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Dirección IP</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Sello de Tiempo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-indigo-50/30 transition-all group">
                            <td class="px-6 py-6 whitespace-nowrap text-left border-r border-slate-50/50">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-xl bg-slate-900 flex items-center justify-center text-[10px] font-black text-white shadow-lg mr-3">
                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-[13px] font-black text-slate-900">{{ $log->user->name ?? 'Sistema' }}</div>
                                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $log->user ? $log->user->getRoleNames()->first() : 'Núcleo' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-left pl-10 border-r border-slate-50/50">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">{{ $log->action }}</span>
                                    <span class="text-[13px] text-slate-600 font-medium leading-relaxed line-clamp-1 truncate max-w-[300px]">{{ $log->description }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-center border-r border-slate-50/50">
                                <div class="px-3 py-1.5 bg-slate-100 rounded-lg border border-slate-200/50 text-[10px] font-mono font-bold text-slate-600 shadow-sm inline-block">
                                    {{ $log->ip_address }}
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-center">
                                <div class="text-[12px] font-black text-slate-900">{{ $log->created_at->format('H:i:s') }}</div>
                                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">{{ $log->created_at->format('d M, Y') }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-10 py-24 text-center">
                                <p class="text-[13px] font-black text-slate-400 uppercase tracking-[0.3em]">No hay registros de auditoría disponibles</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="p-8 bg-white/10">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-[10px] font-black text-white">
                                {{ substr($log->user->name ?? 'S', 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-black text-slate-900">{{ $log->user->name ?? 'Sistema' }}</div>
                                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $log->ip_address }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] font-black text-slate-900">{{ $log->created_at->format('H:i') }}</div>
                            <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">{{ $log->created_at->format('d/m/y') }}</div>
                        </div>
                    </div>

                    <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-100/50">
                        <div class="text-[9px] font-black text-indigo-600 uppercase tracking-widest mb-2">{{ $log->action }}</div>
                        <div class="text-sm text-slate-600 font-medium leading-relaxed">{{ $log->description }}</div>
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Sin registros</p>
                </div>
            @endforelse
        </div>

        <div class="px-10 py-8 bg-slate-50/50 backdrop-blur-md border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
