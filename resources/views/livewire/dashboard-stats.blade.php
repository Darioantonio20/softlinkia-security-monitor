<?php

use Livewire\Volt\Component;
use App\Models\Device;
use App\Models\Incident;
use App\Models\DeviceEvent;
use App\Models\User;

new class extends Component {
    public $clientFilter = '';
    public $typeFilter = '';

    public function with(): array
    {
        $user = auth()->user();
        
        // Base queries
        $devicesQuery = Device::query();
        $incidentsQuery = Incident::whereIn('status', ['pendiente', 'en proceso']);
        $eventsQuery = DeviceEvent::query()->with('device');

        // Apply Global Filters
        if ($this->clientFilter) {
            $devicesQuery->where('client_id', $this->clientFilter);
            $incidentsQuery->whereHas('device', fn($q) => $q->where('client_id', $this->clientFilter));
            $eventsQuery->whereHas('device', fn($q) => $q->where('client_id', $this->clientFilter));
        }

        if ($this->typeFilter) {
            $devicesQuery->where('type', $this->typeFilter);
            $incidentsQuery->whereHas('device', fn($q) => $q->where('type', $this->typeFilter));
            $eventsQuery->whereHas('device', fn($q) => $q->where('type', $this->typeFilter));
        }

        // RBAC: Clients only see their own stats
        if ($user->hasRole('Cliente')) {
            $devicesQuery->where('client_id', $user->id);
            $incidentsQuery->whereHas('device', fn($q) => $q->where('client_id', $user->id));
            $eventsQuery->whereHas('device', fn($q) => $q->where('client_id', $user->id));
        }

        $totalDevices = (clone $devicesQuery)->count();
        $activeDevices = (clone $devicesQuery)->where('status', 'activo')->count();
        $alertDevicesCount = (clone $devicesQuery)->where('status', 'alerta')->count();
        $inactiveDevices = (clone $devicesQuery)->where('status', 'inactivo')->count();

        $healthRate = $totalDevices > 0 ? round(($activeDevices / $totalDevices) * 100) : 0;

        return [
            'totalDevices' => $totalDevices,
            'activeIncidents' => $incidentsQuery->count(),
            'alertDevices' => $alertDevicesCount,
            'recentEvents' => $eventsQuery->latest()->take(8)->get(),
            'clients' => User::role('Cliente')->get(),
            'deviceTypes' => Device::distinct()->pluck('type'),
            'stats' => [
                'active' => $activeDevices,
                'alert' => $alertDevicesCount,
                'inactive' => $inactiveDevices,
                'health' => $healthRate
            ]
        ];
    }
}; ?>

<div>
    <!-- Dashboard Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm mb-8 flex flex-wrap gap-4 items-center border border-gray-100">
        <div class="flex items-center text-sm font-bold text-gray-500 mr-2">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            FILTRAR DASHBOARD:
        </div>
        
        @unless(auth()->user()->hasRole('Cliente'))
        <select wire:model.live="clientFilter" class="text-sm border-gray-200 rounded-lg focus:ring-indigo-500 min-w-[200px]">
            <option value="">Todos los Clientes</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}">{{ $client->name }}</option>
            @endforeach
        </select>
        @endunless

        <select wire:model.live="typeFilter" class="text-sm border-gray-200 rounded-lg focus:ring-indigo-500 min-w-[200px]">
            <option value="">Todos los Tipos de Equipo</option>
            @foreach($deviceTypes as $type)
                <option value="{{ $type }}">{{ $type }}</option>
            @endforeach
        </select>

        <button wire:click="$set('clientFilter', ''); $set('typeFilter', '')" class="text-xs text-gray-400 hover:text-red-500 underline ml-auto">Limpiar Filtros</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Main Stats -->
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 p-6 rounded-2xl shadow-lg text-white">
            <p class="text-indigo-100 text-sm font-medium">Salud del Sistema</p>
            <p class="text-4xl font-bold mt-1">{{ $stats['health'] }}%</p>
            <div class="mt-4 bg-indigo-800 bg-opacity-50 rounded-full h-2">
                <div class="bg-white h-2 rounded-full" style="width: {{ $stats['health'] }}%"></div>
            </div>
            <p class="text-xs mt-3 text-indigo-100 italic">Basado en equipos activos</p>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
            <div class="bg-blue-50 p-3 rounded-xl mr-4 text-blue-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Dispositivos</p>
                <p class="text-2xl font-black text-gray-800">{{ $totalDevices }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
            <div class="bg-red-50 p-3 rounded-xl mr-4 text-red-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Incidencias</p>
                <p class="text-2xl font-black text-gray-800">{{ $activeIncidents }}</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
            <div class="bg-amber-50 p-3 rounded-xl mr-4 text-amber-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">Alertas</p>
                <p class="text-2xl font-black text-gray-800">{{ $alertDevices }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Visual Indicators (CSS Charts) -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-gray-800 font-bold mb-6 flex items-center">
                <span class="bg-indigo-500 w-1.5 h-6 mr-3 rounded-full"></span>
                ESTADO DE LA FLOTA
            </h3>
            
            <div class="space-y-6">
                @php
                    $actPercent = $totalDevices > 0 ? ($stats['active'] / $totalDevices) * 100 : 0;
                    $alePercent = $totalDevices > 0 ? ($stats['alert'] / $totalDevices) * 100 : 0;
                    $inaPercent = $totalDevices > 0 ? ($stats['inactive'] / $totalDevices) * 100 : 0;
                @endphp

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-600">Equipos Operativos ({{ $stats['active'] }})</span>
                        <span class="text-sm font-bold text-indigo-600">{{ round($actPercent) }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-indigo-500 h-3 rounded-full transition-all duration-1000" style="width: {{ $actPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-600">Equipos en Alerta ({{ $stats['alert'] }})</span>
                        <span class="text-sm font-bold text-amber-600">{{ round($alePercent) }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-amber-500 h-3 rounded-full transition-all duration-1000" style="width: {{ $alePercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-semibold text-gray-600">Fuera de Línea ({{ $stats['inactive'] }})</span>
                        <span class="text-sm font-bold text-gray-400">{{ round($inaPercent) }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-3">
                        <div class="bg-gray-400 h-3 rounded-full transition-all duration-1000" style="width: {{ $inaPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Feed -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h3 class="text-gray-800 font-bold flex items-center">
                    <span class="bg-red-500 w-1.5 h-6 mr-3 rounded-full"></span>
                    LOG DE EVENTOS RECIENTES
                </h3>
                <a href="{{ route('incidents') }}" class="text-xs text-indigo-600 font-bold hover:underline">Ver todas las incidencias →</a>
            </div>
            
            <div class="overflow-y-auto max-h-[400px]">
                <table class="min-w-full">
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentEvents as $event)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full {{ str_contains(strtolower($event->type), 'descon') ? 'bg-red-500 animate-pulse' : 'bg-blue-500' }} mr-3"></div>
                                        <span class="text-sm font-semibold text-gray-700">{{ strtoupper($event->type) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs text-gray-500 font-medium">{{ $event->device->name }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-1 rounded font-mono uppercase">{{ $event->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 text-center text-gray-400 italic">No hay actividad reciente en el sistema.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
