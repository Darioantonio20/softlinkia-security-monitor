<?php

use Livewire\Volt\Component;
use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\AuditLog;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $statusFilter = '';
    public $search = '';

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
        ];
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

        session()->flash('status', 'Estado de la incidencia actualizado correctamente.');
    }
}; ?>

<div>
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 bg-green-50 p-4 rounded-lg border border-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-4 w-full">
            <input wire:model.live="search" type="text" placeholder="Buscar por nombre de dispositivo..." 
                   class="w-1/3 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
            
            <select wire:model.live="statusFilter" class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en proceso">En proceso</option>
                <option value="resuelto">Resuelto</option>
            </select>
        </div>
    </div>

    <!-- Incident Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo / Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo / Alerta</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Atendido por</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gestión</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($incidents as $incident)
                    <tr class="{{ $incident->status === 'pendiente' ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $incident->id }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $incident->device->name }}</div>
                            <div class="text-xs text-gray-500">Cliente: {{ $incident->device->client->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $incident->type }}</div>
                            <div class="text-xs text-red-600">{{ $incident->description }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $incident->status === 'pendiente' ? 'bg-red-100 text-red-800' : ($incident->status === 'en proceso' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800') }}">
                                {{ $incident->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $incident->assignedUser->name ?? 'Sin asignar' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                            @if($incident->status !== 'resuelto')
                                <button wire:click="updateStatus({{ $incident->id }}, 'en proceso')" class="text-amber-600 hover:text-amber-900 px-2 py-1 bg-amber-50 rounded">Procesar</button>
                                <button wire:click="updateStatus({{ $incident->id }}, 'resuelto')" class="text-green-600 hover:text-green-900 px-2 py-1 bg-green-50 rounded">Resolver</button>
                            @else
                                <span class="text-green-600 font-bold">✓ Completado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No hay incidencias reportadas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $incidents->links() }}
        </div>
    </div>
</div>
