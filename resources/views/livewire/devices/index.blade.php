<?php

use Livewire\Volt\Component;
use App\Models\Device;
use App\Models\User;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    // Form fields
    public $name = '';
    public $type = '';
    public $location = '';
    public $client_id = '';
    public $status = 'activo';

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
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%');
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
        $this->reset(['name', 'type', 'location', 'client_id', 'status', 'editingDevice']);
        $this->showingModal = true;
    }

    public function edit(Device $device)
    {
        $this->editingDevice = $device;
        $this->name = $device->name;
        $this->type = $device->type;
        $this->location = $device->location;
        $this->client_id = $device->client_id;
        $this->status = $device->status;
        $this->showingModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:3',
            'type' => 'required',
            'location' => 'required',
            'client_id' => 'required|exists:users,id',
            'status' => 'required|in:activo,inactivo,alerta',
        ]);

        if ($this->editingDevice) {
            $this->editingDevice->update([
                'name' => $this->name,
                'type' => $this->type,
                'location' => $this->location,
                'client_id' => $this->client_id,
                'status' => $this->status,
            ]);
        } else {
            Device::create([
                'name' => $this->name,
                'type' => $this->type,
                'location' => $this->location,
                'client_id' => $this->client_id,
                'status' => $this->status,
            ]);
        }

        $this->showingModal = false;
        $this->resetPage();
    }

    public function delete(Device $device)
    {
        $device->delete();
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

    <div class="flex justify-between items-center mb-6">
        <div class="flex gap-4 w-2/3">
            <input wire:model.live="search" type="text" placeholder="Buscar por nombre o ubicación..." 
                   class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            
            <select wire:model.live="statusFilter" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">Todos los estados</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
                <option value="alerta">Alerta</option>
            </select>
        </div>

        @can('gestionar dispositivos')
        <button wire:click="openCreate" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Nuevo Dispositivo
        </button>
        @endcan
    </div>

    <!-- Table -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($devices as $device)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $device->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $device->type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $device->location }}</td>
                        <td class="px-6 py-4 whitespace-nowrap uppercase">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $device->status === 'activo' ? 'bg-green-100 text-green-800' : ($device->status === 'alerta' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $device->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $device->client->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex justify-end gap-2">
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="text-amber-600 hover:text-amber-900 px-2 py-1 bg-amber-50 rounded border border-amber-200">Simular</button>
                                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-xl z-50">
                                    <button wire:click="simulateEvent({{ $device->id }}, 'desconexión')" @click="open = false" class="block w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 font-bold border-b">Simular Desconexión</button>
                                    <button wire:click="simulateEvent({{ $device->id }}, 'anomalía')" @click="open = false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Simular Anomalía</button>
                                    <button wire:click="simulateEvent({{ $device->id }}, 'actividad sospechosa')" @click="open = false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Actividad Sospechosa</button>
                                </div>
                            </div>

                            @can('gestionar dispositivos')
                                <button wire:click="edit({{ $device->id }})" class="text-indigo-600 hover:text-indigo-900 border border-indigo-200 px-2 py-1 rounded bg-indigo-50">Editar</button>
                                <button wire:click="delete({{ $device->id }})" wire:confirm="¿Estás seguro?" class="text-red-600 hover:text-red-900 border border-red-200 px-2 py-1 rounded bg-red-50">Borrar</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No se encontraron dispositivos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="px-6 py-4">
            {{ $devices->links() }}
        </div>
    </div>

    <!-- Modal Simple (Integrado) -->
    @if($showingModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="save" class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingDevice ? 'Editar Dispositivo' : 'Nuevo Dispositivo' }}</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input wire:model="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipo</label>
                            <input wire:model="type" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ubicación</label>
                            <input wire:model="location" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Estado</label>
                            <select wire:model="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="alerta">Alerta</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cliente Asignado</label>
                            <select wire:model="client_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Seleccione un cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="$set('showingModal', false)" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</button>
                        <button type="submit" class="bg-gray-800 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
