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
            $query->whereHas('device', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
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
     @notify.window="notifications.push($event.detail); setTimeout(() => notifications.shift(), 4000)">
    
    <!-- Notifications Overlay -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="note in notifications" :key="note">
            <div class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-2xl border-l-4 border-indigo-500 animate-bounce flex items-center">
                <svg class="w-5 h-5 mr-3 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                </svg>
                <span x-text="note"></span>
            </div>
        </template>
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-4 w-2/3">
            <input wire:model.live="search" type="text" placeholder="Buscar por nombre de dispositivo..." 
                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
            
            <select wire:model.live="statusFilter" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en proceso">En proceso</option>
                <option value="resuelto">Resuelto</option>
            </select>
        </div>

        @can('gestionar incidencias')
        <button wire:click="openManualCreate" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Nueva Incidencia Manual
        </button>
        @endcan
    </div>

    <!-- Incident Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo / Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo / Alerta</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gestión</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($incidents as $incident)
                    <tr class="{{ $incident->status === 'pendiente' ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">#INC-{{ str_pad($incident->id, 5, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $incident->device->name }}</div>
                            <div class="text-xs text-gray-500">Cliente: {{ $incident->device->client->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-semibold">{{ $incident->type }}</div>
                            <div class="text-xs text-gray-600 italic truncate w-48" title="{{ $incident->description }}">{{ $incident->description }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm
                                {{ $incident->status === 'pendiente' ? 'bg-red-100 text-red-800' : ($incident->status === 'en proceso' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">
                                {{ strtoupper($incident->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                            @if($incident->status !== 'resuelto')
                                <button wire:click="updateStatus({{ $incident->id }}, 'en proceso')" class="text-amber-700 hover:text-amber-900 px-3 py-1 bg-amber-50 rounded-md border border-amber-200 transition-colors">Procesar</button>
                                <button wire:click="updateStatus({{ $incident->id }}, 'resuelto')" class="text-green-700 hover:text-green-900 px-3 py-1 bg-green-50 rounded-md border border-green-200 transition-colors">Resolver</button>
                            @else
                                <span class="bg-gray-100 text-gray-400 px-3 py-1 rounded-md text-xs">✓ Finalizada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay incidencias reportadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $incidents->links() }}
        </div>
    </div>

    <!-- Manual Create Modal -->
    @if($showingManualModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showingManualModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <form wire:submit.prevent="saveManualIncident" class="p-8">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-red-100 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Crear Incidencia Manual</h3>
                    </div>
                    
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Dispositivo Afectado</label>
                            <select wire:model="manual_device_id" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                                <option value="">Seleccione el equipo...</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->client->name }})</option>
                                @endforeach
                            </select>
                            @error('manual_device_id') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Tipo de Alerta</label>
                            <input wire:model="manual_type" type="text" placeholder="Ej: Fallo de hardware, Error de energía..."
                                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm">
                            @error('manual_type') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Descripción Detallada</label>
                            <textarea wire:model="manual_description" rows="4" placeholder="Explique el problema detectado..."
                                      class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                            @error('manual_description') <span class="text-red-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showingManualModal', false)" class="bg-white py-2 px-6 border border-gray-300 rounded-lg shadow-sm text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">Cancelar</button>
                        <button type="submit" class="bg-red-600 py-2 px-6 border border-transparent rounded-lg shadow-md text-sm font-bold text-white hover:bg-red-700 transition-transform active:scale-95">Registrar Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
