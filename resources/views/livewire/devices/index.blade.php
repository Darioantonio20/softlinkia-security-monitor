<?php

use Livewire\Volt\Component;
use App\Models\Device;
use App\Models\User;
use App\Livewire\Forms\DeviceForm;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public DeviceForm $form;
    public $search = '';
    public $statusFilter = '';

    public $showingModal = false;
    public $editingDevice = null;

    public function with(): array
    {
        $query = Device::query()->with('client');

        // RBAC logic: Clients only see their devices
        if (auth()->user()->hasRole('Cliente')) {
            $query->where('client_id', auth()->id());
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhere('type', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', function($qu) {
                      $qu->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return [
            'devices' => $query->latest()->paginate(10),
            'clients' => User::role('Cliente')->get(),
        ];
    }

    public function openCreate()
    {
        $this->form->reset();
        $this->editingDevice = null;
        $this->showingModal = true;
    }

    public function edit(Device $device)
    {
        $this->editingDevice = $device;
        $this->form->setDevice($device);
        $this->showingModal = true;
    }

    public function save()
    {
        if ($this->editingDevice) {
            $this->form->update();
        } else {
            $this->form->store();
        }

        $this->showingModal = false;
        $this->resetPage();
    }

    public function delete(Device $device)
    {
        $name = $device->name;
        $device->delete();

        \App\Models\AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'DEVICE_DELETED',
            'description' => "Se eliminó el dispositivo: {$name}",
            'ip_address' => request()->ip(),
        ]);
    }

    public function simulateEvent(Device $device, $type)
    {
        \App\Models\DeviceEvent::create([
            'device_id' => $device->id,
            'type' => $type,
            'timestamp' => now(),
        ]);

        session()->flash('status', "Evento '$type' simulado para {$device->name}.");
    }
}; ?>

<div>
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-8 mb-12">
        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
            <div class="relative flex-1 sm:min-w-[400px] group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input wire:model.live="search" type="text" placeholder="Buscar terminal por nombre o ubicación..." 
                       class="w-full pl-14 pr-12 py-4 bg-white border border-slate-200 rounded-3xl shadow-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-sm font-bold text-slate-800 placeholder-slate-400">
                
                <!-- Loading Spinner -->
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-4 flex items-center pr-2">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            
            <select wire:model.live="statusFilter" class="bg-white border border-slate-200 rounded-3xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all text-[11px] font-black uppercase tracking-widest text-slate-600 px-8 py-4 cursor-pointer shadow-sm">
                <option value="">Todos los Estados</option>
                <option value="activo">En Línea</option>
                <option value="inactivo">Fuera de Servicio</option>
                <option value="alerta">Atención Requerida</option>
            </select>
        </div>

        @hasrole(['Administrador', 'Operador'])
        <div class="flex gap-4">
            <a href="{{ route('reports.devices.pdf') }}" class="inline-flex items-center px-6 py-4 bg-slate-900 text-white rounded-[1.5rem] font-black text-[10px] uppercase tracking-[0.2em] shadow-lg hover:-translate-y-1 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
            </a>
            <a href="{{ route('reports.devices.csv') }}" class="inline-flex items-center px-6 py-4 bg-white border border-slate-200 text-slate-700 rounded-[1.5rem] font-black text-[10px] uppercase tracking-[0.2em] shadow-sm hover:-translate-y-1 transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                CSV
            </a>
        </div>
        @endhasrole

        @can('crear dispositivos')
        <button wire:click="openCreate" class="inline-flex items-center px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.2em] shadow-[0_15px_30px_-5px_rgba(79,70,229,0.4)] hover:shadow-[0_20px_40px_-5px_rgba(79,70,229,0.5)] transition-all hover:-translate-y-1 active:scale-95">
            <svg class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
            Agregar Terminal
        </button>
        @endcan
    </div>

    <!-- Table container with glass effect -->
    <div class="glass overflow-hidden border-white/40 rounded-[2.5rem] shadow-2xl premium-card">
        <!-- Desktop Table -->
        <div class="hidden lg:block relative group/table" 
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
                    class="absolute left-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-md border border-indigo-400 hover:bg-indigo-700 transition-colors duration-300 group">
                <svg class="w-6 h-6 group-active:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
            </button>
            
            <button @click="scroll(1)" x-show="!scrolledRight" x-transition 
                    class="absolute right-4 top-1/2 -translate-y-1/2 z-50 p-4 bg-indigo-600 text-white rounded-2xl shadow-md border border-indigo-400 hover:bg-indigo-700 transition-colors duration-300 group">
                <svg class="w-6 h-6 group-active:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="overflow-x-auto" x-ref="scrollContainer" @scroll.throttle.50ms="checkScroll()">
                <table class="min-w-full divide-y divide-slate-100/50">
                <thead class="bg-slate-50/50 backdrop-blur-md">
                    <tr>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Dispositivo de Seguridad</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Especificaciones</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Localización</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Estado</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Propietario</th>
                        <th class="px-6 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Gestión</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @forelse($devices as $device)
                        <tr class="hover:bg-indigo-50/30 transition-all group">
                            <td class="px-6 py-6 whitespace-nowrap text-left border-r border-slate-50/50">
                                <div class="flex items-center">
                                    <div class="p-2.5 bg-indigo-600 text-white rounded-xl mr-4 shadow-sm">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </div>
                                    <span class="text-[14px] font-black text-slate-900 tracking-tight">{{ $device->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-left text-[10px] font-black text-slate-500 uppercase tracking-widest pl-10 border-r border-slate-50/50">{{ $device->type }}</td>
                            <td class="px-6 py-6 whitespace-nowrap text-left pl-10 border-r border-slate-50/50">
                                <div class="flex items-center text-[13px] font-bold text-slate-600">
                                    <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.914c-.488.488-1.219.488-1.707 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $device->location }}
                                </div>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-center border-r border-slate-50/50">
                                @php
                                    $statusClasses = [
                                        'activo' => 'bg-emerald-500 text-white shadow-lg shadow-emerald-200',
                                        'alerta' => 'bg-rose-600 text-white shadow-lg shadow-rose-200 animate-pulse',
                                        'inactivo' => 'bg-slate-400 text-white shadow-lg shadow-slate-200',
                                    ];
                                @endphp
                                <span class="px-3 py-1.5 inline-flex text-[8px] font-black rounded-full uppercase tracking-widest {{ $statusClasses[$device->status] ?? $statusClasses['inactivo'] }}">
                                    {{ $device->status }}
                                </span>
                            </td>
                            <td class="px-6 py-6 whitespace-nowrap text-center text-[10px] font-black text-slate-500 uppercase tracking-widest border-r border-slate-50/50">{{ $device->client->name }}</td>
                            <td class="px-6 py-6 whitespace-nowrap">
                                <div class="flex justify-center gap-2">
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click="open = !open" class="text-[9px] font-black uppercase tracking-widest text-amber-700 bg-amber-50 px-3 py-2 rounded-xl border border-amber-200 hover:bg-amber-100 transition-all flex items-center gap-1.5">
                                            Simular
                                            <svg class="w-2.5 h-2.5 transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="open" @click.away="open = false" 
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                             class="absolute right-0 mt-3 w-56 glass border-white/40 rounded-2xl shadow-2xl z-50 overflow-hidden">
                                            <button wire:click="simulateEvent({{ $device->id }}, 'desconexión')" @click="open = false" class="flex items-center gap-3 w-full text-left px-6 py-4 text-[10px] font-black uppercase text-rose-600 hover:bg-rose-50 border-b border-slate-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 12.728l3.536-3.536M12 3v1m0 16v1m9-9h-1M3 12h1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707-.707"/></svg>
                                                Desconexión
                                            </button>
                                            <button wire:click="simulateEvent({{ $device->id }}, 'anomalía')" @click="open = false" class="flex items-center gap-3 w-full text-left px-6 py-4 text-[10px] font-black uppercase text-amber-600 hover:bg-amber-50 border-b border-slate-100 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                Anomalía
                                            </button>
                                            <button wire:click="simulateEvent({{ $device->id }}, 'actividad sospechosa')" @click="open = false" class="flex items-center gap-3 w-full text-left px-6 py-4 text-[10px] font-black uppercase text-indigo-600 hover:bg-indigo-50 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                                Sospechoso
                                            </button>
                                        </div>
                                    </div>
        
                                    @can('editar dispositivos')
                                        <button wire:click="edit({{ $device->id }})" class="p-2.5 text-indigo-600 hover:text-white hover:bg-indigo-600 rounded-xl border border-indigo-100 transition-all duration-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                    @endcan

                                    @can('eliminar dispositivos')
                                        <button wire:click="delete({{ $device->id }})" wire:confirm="¿Estás seguro?" class="p-2.5 text-rose-600 hover:text-white hover:bg-rose-600 rounded-xl border border-rose-100 transition-all duration-500">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-10 py-24 text-center">
                                <div class="flex flex-col items-center gap-6">
                                    <div class="p-8 bg-slate-50 rounded-[2.5rem] text-slate-200 border border-slate-100">
                                        <svg class="w-20 h-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </div>
                                    <p class="text-[13px] font-black text-slate-400 uppercase tracking-[0.3em]">No se han detectado terminales activos</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($devices as $device)
                <div class="p-8 bg-white/10">
                    <div class="flex justify-between items-center mb-6">
                        <div class="flex items-center gap-4">
                            <div class="p-2.5 bg-indigo-600 text-white rounded-xl shadow-lg">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <span class="text-lg font-black text-slate-900 tracking-tight">{{ $device->name }}</span>
                        </div>
                        @php
                            $statusClasses = [
                                'activo' => 'bg-emerald-500 text-white',
                                'alerta' => 'bg-rose-600 text-white animate-pulse',
                                'inactivo' => 'bg-slate-400 text-white',
                            ];
                        @endphp
                        <span class="px-3 py-1.5 text-[9px] font-black rounded-full uppercase tracking-widest shadow-sm {{ $statusClasses[$device->status] ?? $statusClasses['inactivo'] }}">
                            {{ $device->status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Tipo</div>
                            <div class="text-xs font-black text-slate-800 uppercase">{{ $device->type }}</div>
                        </div>
                        <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Área</div>
                            <div class="text-xs font-black text-slate-800">{{ $device->location }}</div>
                        </div>
                        <div class="bg-slate-50/50 p-4 rounded-2xl border border-slate-100/50 col-span-2">
                            <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Cliente</div>
                            <div class="text-xs font-black text-slate-800">{{ $device->client->name }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <div x-data="{ open: false }" class="flex-1 relative">
                            <button @click="open = !open" class="w-full py-3 text-[10px] font-black uppercase text-amber-700 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-center gap-2">
                                Simular
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute left-0 bottom-full mb-3 w-full glass border-white/40 rounded-2xl shadow-2xl z-50 overflow-hidden">
                                <button wire:click="simulateEvent({{ $device->id }}, 'desconexión')" @click="open = false" class="flex items-center gap-3 w-full text-left px-6 py-4 text-[10px] font-black uppercase text-rose-600 hover:bg-rose-50 border-b border-slate-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 12.728l3.536-3.536M12 3v1m0 16v1m9-9h-1M3 12h1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707-.707"/></svg>
                                    Desconexión
                                </button>
                                <button wire:click="simulateEvent({{ $device->id }}, 'anomalía')" @click="open = false" class="flex items-center gap-3 w-full text-left px-6 py-4 text-[10px] font-black uppercase text-amber-600 hover:bg-amber-50 border-b border-slate-100 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Anomalía
                                </button>
                                <button wire:click="simulateEvent({{ $device->id }}, 'actividad sospechosa')" @click="open = false" class="flex items-center gap-3 w-full text-left px-6 py-4 text-[10px] font-black uppercase text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    Sospechoso
                                </button>
                            </div>
                        </div>
                        @can('editar dispositivos')
                            <button wire:click="edit({{ $device->id }})" class="p-3 text-indigo-600 bg-indigo-50 rounded-xl border border-indigo-200 shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </button>
                        @endcan
                        @can('eliminar dispositivos')
                            <button wire:click="delete({{ $device->id }})" wire:confirm="¿Estás seguro?" class="p-3 text-rose-600 bg-rose-50 rounded-xl border border-rose-200 shadow-sm">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="p-20 text-center">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">No se detectaron terminales</p>
                </div>
            @endforelse
        </div>
        
        <div class="px-10 py-8 bg-slate-50/50 backdrop-blur-md border-t border-slate-100">
            {{ $devices->links('components.pagination-premium') }}
        </div>
    </div>

    <!-- Premium Modal -->
    @if($showingModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto" x-data x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" wire:click="$set('showingModal', false)"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-middle glass rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border-gray-200/50">
                <form wire:submit.prevent="save" class="relative">
                    <!-- Modal Header -->
                    <div class="px-10 pt-10 pb-6">
                        <h3 class="text-2xl font-black text-gray-800 tracking-tight flex items-center gap-3">
                            <div class="p-2 bg-indigo-600 text-white rounded-xl shadow-lg shadow-indigo-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            {{ $editingDevice ? 'Editar Dispositivo' : 'Nuevo Dispositivo' }}
                        </h3>
                        <p class="mt-2 text-[11px] font-bold text-gray-400 uppercase tracking-widest">Complete los detalles técnicos del equipo</p>
                    </div>

                    <div class="px-10 pb-10 space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Identificador / Nombre</label>
                                <input wire:model="form.name" type="text" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 p-3.5 font-bold transition-all" placeholder="Ej: Cámara Pasillo Norte">
                                @error('form.name') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Categoría</label>
                                <input wire:model="form.type" type="text" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 p-3.5 font-bold transition-all" placeholder="Ej: Cámara">
                                @error('form.type') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Ubicación</label>
                                <input wire:model="form.location" type="text" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 p-3.5 font-bold transition-all" placeholder="Ej: Entrada Principal">
                                @error('form.location') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Estado Operativo</label>
                                <select wire:model="form.status" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 p-3.5 font-bold transition-all">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="alerta">Alerta</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">Cliente Responsable</label>
                                <select wire:model="form.client_id" class="w-full bg-white/50 border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 p-3.5 font-bold transition-all">
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.client_id') <span class="mt-1 text-rose-500 text-[10px] font-black uppercase tracking-tight px-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-10 flex gap-4">
                            <button type="button" wire:click="$set('showingModal', false)" 
                                    class="flex-1 px-6 py-4 border border-gray-200 rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition-all">
                                Descartar
                            </button>
                            <button type="submit" 
                                    class="flex-[2] px-6 py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-[0_10px_20px_-5px_rgba(79,70,229,0.4)] hover:shadow-[0_15px_25px_-5px_rgba(79,70,229,0.5)] transition-all">
                                {{ $editingDevice ? 'Guardar Cambios' : 'Confirmar Registro' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
