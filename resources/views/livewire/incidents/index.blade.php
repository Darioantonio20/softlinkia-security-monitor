<?php

use Livewire\Volt\Component;
use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\AuditLog;
use App\Models\Device;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

    // Manual Creation Fields
    public $showingManualModal = false;
    public $manual_device_id = '';
    public $manual_type = '';
    public $manual_description = '';

    // History View Fields
    public $showingHistoryModal = false;
    public $selectedIncident = null;

    public function with(): array
    {
        $query = Incident::query()->with(['device.client', 'assignedUser']);

        if (auth()->user()->hasRole('Cliente')) {
            $query->whereHas('device', function($q) {
                $q->where('client_id', auth()->id());
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('type', 'like', '%' . $this->search . '%')
                  ->orWhereHas('device', function($qu) {
                      $qu->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('location', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return [
            'incidents' => $query->latest()->paginate(10),
            'devices' => Device::all(),
        ];
    }

    public function openManualCreate()
    {
        $this->reset(['manual_device_id', 'manual_type', 'manual_description']);
        $this->showingManualModal = true;
    }

    public function saveManualIncident()
    {
        $this->validate([
            'manual_device_id' => 'required|exists:devices,id',
            'manual_type' => 'required',
            'manual_description' => 'required|min:10',
        ]);

        $incident = Incident::create([
            'device_id' => $this->manual_device_id,
            'type' => $this->manual_type,
            'status' => 'pendiente',
            'description' => $this->manual_description,
            'assigned_user_id' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'INCIDENCIA_MANUAL_CREADA',
            'description' => "Se creó manualmente la incidencia #{$incident->id} para el dispositivo ID: {$this->manual_device_id}",
            'ip_address' => request()->ip(),
        ]);

        $this->showingManualModal = false;
        session()->flash('status', 'Incidencia creada manualmente con éxito.');
        $this->dispatch('notify', 'Nueva incidencia manual registrada.');
    }

    public function updateStatus($incidentId, $newStatus)
    {
        $incident = Incident::findOrFail($incidentId);
        $oldStatus = $incident->status;

        $incident->update([
            'status' => $newStatus,
            'assigned_user_id' => auth()->id(),
        ]);

        // Guardar en el historial de la incidencia
        IncidentHistory::create([
            'incident_id' => $incident->id,
            'status_before' => $oldStatus,
            'status_after' => $newStatus,
            'user_id' => auth()->id(),
            'comments' => 'Estado actualizado desde el panel de gestión.',
        ]);

        // Registrar en la Bitácora (Audit Log)
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'CAMBIO_ESTADO_INCIDENCIA',
            'description' => "Se actualizó la incidencia #{$incident->id} de {$oldStatus} a {$newStatus}.",
            'ip_address' => request()->ip(),
            'metadata' => ['incident_id' => $incident->id],
        ]);

        $this->dispatch('notify', "Incidencia #{$incident->id} actualizada a {$newStatus}.");
        session()->flash('status', 'Estado de la incidencia actualizado correctamente.');
    }

    protected function messages()
    {
        return [
            'manual_device_id.required' => 'Debe seleccionar un dispositivo del inventario.',
            'manual_type.required' => 'El tipo de alerta es obligatorio para el protocolo.',
            'manual_description.required' => 'La descripción es necesaria para el equipo de respuesta.',
            'manual_description.min' => 'La descripción debe ser detallada (mínimo 10 caracteres).',
        ];
    }

    public function viewHistory($incidentId)
    {
        $this->selectedIncident = Incident::with(['history.user', 'device'])->findOrFail($incidentId);
        $this->showingHistoryModal = true;
    }
}; ?>

<div x-data="{ notifications: [] }" 
     @notify.window="notifications.push($event.detail); setTimeout(() => notifications.shift(), 5000)">
    
    <!-- Notifications Overlay (Premium Toast) -->
    <div class="fixed bottom-10 right-10 z-[100] space-y-5">
        <template x-for="note in notifications" :key="note">
            <div x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-20 scale-90 blur-lg"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100 blur-0"
                 class="liquid-glass px-10 py-6 rounded-[2.5rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.2)] border-white/50 flex items-center gap-6 min-w-[380px] group">
                <div class="p-3 bg-indigo-600 rounded-2xl shadow-sm transition-colors duration-500">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-[9px] font-black uppercase tracking-[0.3em] text-indigo-700">Protocolo de Notificación</p>
                    <span class="text-[15px] font-black text-slate-900 tracking-tight block mt-1" x-text="note"></span>
                </div>
            </div>
        </template>
    </div>

    @if (session('status'))
        <div class="mb-10 glass p-6 rounded-3xl border-emerald-500/20 flex items-center gap-5 animate-in fade-in slide-in-from-top-6 duration-700">
            <div class="p-3 bg-emerald-500 text-white rounded-2xl shadow-xl shadow-emerald-500/20">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-base font-black text-emerald-900 tracking-tight">{{ session('status') }}</span>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-8 mb-12">
        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            <div class="relative flex-1 sm:min-w-[400px] group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-rose-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="Localizar incidente por equipo..." 
                       class="w-full pl-14 pr-12 py-4 bg-white border border-slate-200 rounded-3xl shadow-sm focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all text-sm font-bold text-slate-800 placeholder-slate-400">
                
                <!-- Loading Spinner -->
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-4 flex items-center pr-2">
                    <svg class="animate-spin h-5 w-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            
            <select wire:model.live="statusFilter" class="bg-white border border-slate-200 rounded-3xl focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500 transition-all text-[11px] font-black uppercase tracking-widest text-slate-600 px-8 py-4 cursor-pointer shadow-sm">
                <option value="">Filtro de Estado</option>
                <option value="pendiente">Crítico (Pendiente)</option>
                <option value="en proceso">En Intervención</option>
                <option value="resuelto">Cerrado / Auditado</option>
            </select>
        </div>

        @can('crear incidencias')
        <button wire:click="openManualCreate" class="inline-flex items-center px-10 py-4 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.2em] shadow-[0_15px_30px_-5px_rgba(225,29,72,0.4)] hover:shadow-[0_20px_40px_-5px_rgba(225,29,72,0.5)] transition-all hover:-translate-y-1 active:scale-95">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            Despachar Incidencia
        </button>
        @endcan
    </div>

    <!-- Incident Table -->
    <div class="glass overflow-hidden border-white/40 rounded-[2.5rem] shadow-2xl premium-card">
        <!-- Desktop/Tablet Table -->
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
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-[0_10px_30px_-5px_rgba(79,70,229,0.5)] border border-indigo-400 hover:bg-indigo-700 hover:scale-105 transition-all duration-700 group">
                <svg class="w-6 h-6 group-active:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
            </button>
            
            <button @click="scroll(1)" x-show="!scrolledRight" x-transition 
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-md border border-indigo-400 hover:bg-indigo-700 transition-colors duration-500 group">
                <svg class="w-6 h-6 group-active:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="overflow-x-auto" x-ref="scrollContainer" @scroll.throttle.50ms="checkScroll()">
                <table class="min-w-full divide-y divide-slate-100/50 text-left">
                <thead class="bg-slate-50/50 backdrop-blur-md">
                    <tr>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Ticker ID</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Fuente de Seguridad</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Detalles de Evento</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Estado Operativo</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Control</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @forelse($incidents as $incident)
                        <tr class="transition-all group {{ $incident->status === 'pendiente' ? 'bg-rose-50/40' : 'hover:bg-indigo-50/30' }}">
                            <td class="px-6 py-6 whitespace-nowrap text-center border-r border-slate-50/50">
                                <span class="text-[10px] font-black text-indigo-700 bg-indigo-100/60 px-3 py-1.5 rounded-xl border border-indigo-200/50 shadow-sm">#{{ str_pad($incident->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="px-6 py-6 text-left border-r border-slate-50/50">
                                <div class="text-[14px] font-black text-slate-900 tracking-tight">{{ $incident->device->name }}</div>
                                <div class="text-[9px] font-black text-slate-500 uppercase tracking-[0.1em] mt-1">{{ $incident->device->client->name }}</div>
                            </td>
                            <td class="px-6 py-6 text-left border-r border-slate-50/50">
                                <div class="text-[10px] font-black text-slate-800 uppercase tracking-widest">{{ $incident->type }}</div>
                                <div class="text-[13px] text-slate-500 font-medium mt-1 line-clamp-1 max-w-[240px]" title="{{ $incident->description }}">{{ $incident->description }}</div>
                            </td>
                            <td class="px-6 py-6 text-center border-r border-slate-50/50">
                                @php
                                    $statusClasses = [
                                        'pendiente' => 'bg-rose-600 text-white shadow-lg shadow-rose-200 ring-rose-300 animate-pulse',
                                        'en proceso' => 'bg-amber-500 text-white shadow-lg shadow-amber-200 ring-amber-300',
                                        'resuelto' => 'bg-emerald-500 text-white shadow-lg shadow-emerald-200 ring-emerald-300',
                                    ];
                                @endphp
                                <span class="px-3 py-1.5 inline-flex text-[8px] font-black rounded-full uppercase tracking-widest shadow-sm {{ $statusClasses[$incident->status] }}">
                                    {{ $incident->status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-2">
                                    @can('editar incidencias')
                                        @if($incident->status !== 'resuelto')
                                            <button wire:click="updateStatus({{ $incident->id }}, 'en proceso')" 
                                                    class="px-4 py-2 text-[9px] font-black uppercase tracking-widest text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg hover:-translate-y-0.5">
                                                Asumir
                                            </button>
                                            <button wire:click="updateStatus({{ $incident->id }}, 'resuelto')" 
                                                    class="px-4 py-2 text-[9px] font-black uppercase tracking-widest text-white bg-emerald-600 rounded-xl hover:bg-emerald-500 transition-all shadow-md">
                                                Cerrar
                                            </button>
                                        @else
                                            <div class="flex items-center gap-1.5 text-emerald-700 font-black text-[9px] uppercase tracking-widest bg-emerald-100/50 py-2 px-4 rounded-xl border border-emerald-200/50">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Finalizado
                                            </div>
                                        @endif
                                    @endcan

                                    <button wire:click="viewHistory({{ $incident->id }})" 
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all"
                                            title="Ver Historial">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-10 py-24 text-center">
                                <div class="flex flex-col items-center gap-6">
                                    <div class="p-8 bg-slate-50 rounded-[2.5rem] text-slate-200 border border-slate-100">
                                        <svg class="w-20 h-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                    <p class="text-[13px] font-black text-slate-400 uppercase tracking-[0.3em]">Entorno Seguro: Sin Amenazas Activas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($incidents as $incident)
                <div class="p-8 {{ $incident->status === 'pendiente' ? 'bg-rose-50/40' : 'bg-white/10' }}">
                    <div class="flex justify-between items-start mb-6">
                        <span class="text-[10px] font-black text-indigo-700 bg-indigo-100 px-3 py-1.5 rounded-lg border border-indigo-200">#{{ str_pad($incident->id, 5, '0', STR_PAD_LEFT) }}</span>
                        @php
                            $statusClasses = [
                                'pendiente' => 'bg-rose-600 text-white shadow-lg shadow-rose-200',
                                'en proceso' => 'bg-amber-500 text-white shadow-lg shadow-amber-200',
                                'resuelto' => 'bg-emerald-500 text-white shadow-lg shadow-emerald-200',
                            ];
                        @endphp
                        <span class="px-3 py-1.5 text-[9px] font-black rounded-full uppercase tracking-widest {{ $statusClasses[$incident->status] }}">
                            {{ $incident->status }}
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <div class="text-lg font-black text-slate-900 tracking-tight">{{ $incident->device->name }}</div>
                        <div class="text-[10px] font-black text-slate-500 uppercase tracking-widest mt-1">{{ $incident->device->client->name }}</div>
                    </div>

                    <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50 mb-6">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Tipo de Evento</div>
                        <div class="text-xs font-black text-slate-800 uppercase tracking-widest mb-2">{{ $incident->type }}</div>
                        <div class="text-sm text-slate-500 font-medium leading-relaxed">{{ $incident->description }}</div>
                    </div>

                    <div class="flex gap-3">
                        @can('editar incidencias')
                            @if($incident->status !== 'resuelto')
                                <button wire:click="updateStatus({{ $incident->id }}, 'en proceso')" 
                                        class="flex-1 py-3.5 text-[10px] font-black uppercase tracking-widest text-white bg-slate-900 rounded-2xl shadow-lg active:scale-95 transition-all">
                                    Asumir
                                </button>
                                <button wire:click="updateStatus({{ $incident->id }}, 'resuelto')" 
                                        class="flex-1 py-3.5 text-[10px] font-black uppercase tracking-widest text-white bg-emerald-600 rounded-2xl shadow-lg active:scale-95 transition-all">
                                    Cerrar
                                </button>
                            @else
                                <div class="w-full text-center py-3.5 text-[10px] font-black uppercase tracking-widest text-emerald-700 bg-emerald-50 rounded-2xl border border-emerald-100">
                                    Finalizado
                                </div>
                            @endif
                        @endcan
                        
                        <button wire:click="viewHistory({{ $incident->id }})" class="p-3.5 bg-slate-100 text-slate-500 rounded-2xl border border-slate-200 shadow-sm active:scale-90">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Sin Amenazas Activas</p>
                </div>
            @endforelse
        </div>

        <div class="px-10 py-8 bg-slate-50/50 backdrop-blur-md border-t border-slate-100">
            {{ $incidents->links('components.pagination-premium') }}
        </div>
    </div>

    <!-- Manual Create Modal -->
    @if($showingManualModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto" x-data x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" wire:click="$set('showingManualModal', false)"></div>
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showingManualModal', false)"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-middle glass rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border-gray-200/50">
                <form wire:submit.prevent="saveManualIncident" class="relative">
                    <!-- Modal Header -->
                    <div class="px-10 pt-10 pb-6">
                        <h3 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                            <div class="p-2 bg-rose-600 text-white rounded-xl shadow-lg shadow-rose-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            Despacho Manual de Incidencia
                        </h3>
                        <p class="mt-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Apertura manual de ticket de seguridad</p>
                    </div>

                    {{-- Guía de Despacho --}}
                    <div class="px-10 mb-6">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex gap-4">
                            <div class="text-indigo-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-indigo-700 uppercase tracking-widest mb-1">Guía de Despacho</p>
                                <p class="text-[12px] text-indigo-900/70 font-medium leading-relaxed">
                                    Asegúrese de identificar el dispositivo correcto. La descripción debe incluir **qué sucede**, **dónde sucede** y cualquier observación relevante para el técnico.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="px-10 pb-10 space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1 text-left">Dispositivo Afectado</label>
                            <select wire:model="manual_device_id" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 p-3.5 font-bold transition-all">
                                <option value="">Seleccione el equipo en alerta...</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->client->name }})</option>
                                @endforeach
                            </select>
                            @error('manual_device_id') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1 text-left">Tipo de Alerta</label>
                            <input wire:model="manual_type" type="text" placeholder="Ej: Fallo de conexión, Detección de humo..."
                                   class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 p-3.5 font-bold transition-all">
                            @error('manual_type') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1 text-left">Descripción Detallada</label>
                            <textarea wire:model="manual_description" rows="4" placeholder="Escriba los detalles observados del evento..."
                                      class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 p-3.5 font-bold transition-all"></textarea>
                            @error('manual_description') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-10 flex gap-4 text-left">
                            <button type="button" wire:click="$set('showingManualModal', false)" 
                                    class="flex-1 px-6 py-4 border border-gray-200 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                                Descartar
                            </button>
                            <button type="submit" 
                                    class="flex-[2] px-6 py-4 bg-rose-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-[0_10px_20px_-5px_rgba(225,29,72,0.4)] hover:shadow-[0_15px_25px_-5px_rgba(225,29,72,0.5)] transition-all">
                                Registrar Ticket
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- History Timeline Sidebar (Slide-over) -->
    @if($showingHistoryModal && $selectedIncident)
    <div class="fixed inset-0 z-[110] overflow-hidden" x-data x-transition>
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('showingHistoryModal', false)"></div>
        <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div class="pointer-events-auto w-screen max-w-md transform transition-all duration-500 ease-in-out sm:duration-700 translate-x-0">
                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-2xl rounded-l-[3rem]">
                    <div class="bg-indigo-600 px-8 py-10">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-black text-white uppercase tracking-[0.2em]">Historial Técnico</h2>
                            <button wire:click="$set('showingHistoryModal', false)" class="text-white/70 hover:text-white transition-colors">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="bg-white/10 rounded-2xl p-4 backdrop-blur-md border border-white/20">
                            <p class="text-[9px] font-black text-white/60 uppercase tracking-widest mb-1">Incidencia</p>
                            <p class="text-lg font-black text-white">#{{ str_pad($selectedIncident->id, 5, '0', STR_PAD_LEFT) }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span class="text-[10px] font-bold text-white/80">{{ $selectedIncident->device->name }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="relative flex-1 px-8 py-10">
                        <div class="absolute left-10 top-10 bottom-10 w-[2px] bg-slate-100"></div>
                        
                        <div class="space-y-10 relative">
                            {{-- Registro Inicial (Apertura) --}}
                            <div class="relative pl-12">
                                <div class="absolute -left-[1.35rem] top-1 w-5 h-5 rounded-full bg-indigo-600 border-4 border-white shadow-md"></div>
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-1">Apertura de Ticket</span>
                                    <span class="text-[13px] font-bold text-slate-800">Se registró la incidencia inicial</span>
                                    <div class="flex items-center gap-2 mt-2 text-[11px] font-bold text-slate-400">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $selectedIncident->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Cambios de Estado --}}
                            @foreach($selectedIncident->history->sortByDesc('created_at') as $record)
                                <div class="relative pl-12 group">
                                    <div class="absolute -left-[1.35rem] top-1 w-5 h-5 rounded-full bg-slate-200 border-4 border-white shadow-sm group-hover:bg-indigo-400 transition-colors"></div>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="text-[9px] font-black text-slate-400 uppercase line-through">{{ $record->status_before }}</span>
                                            <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded-md">{{ $record->status_after }}</span>
                                        </div>
                                        <p class="text-[13px] text-slate-600 font-medium italic">"{{ $record->comments }}"</p>
                                        <div class="flex items-center gap-4 mt-3">
                                            <div class="flex items-center gap-1.5 text-[10px] font-bold text-slate-400">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ $record->user->name }}
                                            </div>
                                            <div class="text-[10px] font-bold text-slate-400">
                                                {{ $record->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="p-8 border-t border-slate-50">
                        <button wire:click="$set('showingHistoryModal', false)" class="w-full py-4 bg-slate-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-xl hover:bg-slate-800 transition-all">
                            Cerrar Auditoría
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
