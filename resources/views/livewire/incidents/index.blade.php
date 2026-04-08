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
                <div class="p-3 bg-indigo-600 rounded-2xl shadow-xl shadow-indigo-500/30 group-hover:scale-110 transition-transform">
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

        @can('gestionar incidencias')
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
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-[0_10px_30px_-5px_rgba(79,70,229,0.5)] border border-indigo-400 hover:bg-indigo-700 hover:scale-110 transition-all duration-300 group">
                <svg class="w-6 h-6 group-active:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
            </button>
            
            <button @click="scroll(1)" x-show="!scrolledRight" x-transition 
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-[0_10px_30px_-5px_rgba(79,70,229,0.5)] border border-indigo-400 hover:bg-indigo-700 hover:scale-110 transition-all duration-300 group">
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
                                    @if($incident->status !== 'resuelto')
                                        <button wire:click="updateStatus({{ $incident->id }}, 'en proceso')" 
                                                class="px-4 py-2 text-[9px] font-black uppercase tracking-widest text-white bg-slate-900 rounded-xl hover:bg-slate-800 transition-all shadow-lg hover:-translate-y-0.5">
                                            Asumir
                                        </button>
                                        <button wire:click="updateStatus({{ $incident->id }}, 'resuelto')" 
                                                class="px-4 py-2 text-[9px] font-black uppercase tracking-widest text-white bg-emerald-600 rounded-xl hover:bg-emerald-500 transition-all shadow-lg hover:-translate-y-0.5">
                                            Cerrar
                                        </button>
                                    @else
                                        <div class="flex items-center gap-1.5 text-emerald-700 font-black text-[9px] uppercase tracking-widest bg-emerald-100/50 py-2 px-4 rounded-xl border border-emerald-200/50">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Finalizado
                                        </div>
                                    @endif
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
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Sin Amenazas Activas</p>
                </div>
            @endforelse
        </div>

        <div class="px-10 py-8 bg-slate-50/50 backdrop-blur-md border-t border-slate-100">
            {{ $incidents->links() }}
        </div>
    </div>

    <!-- Manual Create Modal -->
    @if($showingManualModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto" x-data x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" wire:click="$set('showingManualModal', false)"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-middle glass rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border-gray-200/50">
                <form wire:submit.prevent="saveManualIncident" class="relative">
                    <!-- Modal Header -->
                    <div class="px-10 pt-10 pb-6">
                        <h3 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                            <div class="p-2 bg-rose-600 text-white rounded-xl shadow-lg shadow-rose-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </div>
                            Manual Incident Dispatch
                        </h3>
                        <p class="mt-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Apertura manual de ticket de seguridad</p>
                    </div>

                    <div class="px-10 pb-10 space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1 text-left">Dispositivo Afectado</label>
                            <select wire:model="manual_device_id" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 p-3.5 font-bold transition-all">
                                <option value="">Seleccione el equipo...</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->client->name }})</option>
                                @endforeach
                            </select>
                            @error('manual_device_id') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1 text-left">Tipo de Alerta</label>
                            <input wire:model="manual_type" type="text" placeholder="Ej: Fallo de hardware, Error de energía..."
                                   class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 p-3.5 font-bold transition-all">
                            @error('manual_type') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1 text-left">Descripción Detallada</label>
                            <textarea wire:model="manual_description" rows="4" placeholder="Explique el problema detectado..."
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
</div>
