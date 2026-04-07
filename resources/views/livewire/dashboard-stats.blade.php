<?php

use Livewire\Volt\Component;
use App\Models\Device;
use App\Models\Incident;
use App\Models\DeviceEvent;

new class extends Component {
    public function with(): array
    {
        $user = auth()->user();
        
        // Base queries for stats
        $devicesQuery = Device::query();
        $incidentsQuery = Incident::whereIn('status', ['pendiente', 'en proceso']);
        $eventsQuery = DeviceEvent::query()->with('device');

        // RBAC: Clients only see their own stats
        if ($user->hasRole('Cliente')) {
            $devicesQuery->where('client_id', $user->id);
            $incidentsQuery->whereHas('device', fn($q) => $q->where('client_id', $user->id));
            $eventsQuery->whereHas('device', fn($q) => $q->where('client_id', $user->id));
        }

        return [
            'totalDevices' => $devicesQuery->count(),
            'activeIncidents' => $incidentsQuery->count(),
            'alertDevices' => $devicesQuery->where('status', 'alerta')->count(),
            'recentEvents' => $eventsQuery->latest()->take(5)->get(),
        ];
    }
}; ?>

<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Stat Card: Devices -->
        <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-indigo-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="mb-2 text-sm font-medium text-gray-600">Total Dispositivos</p>
                    <p class="text-2xl font-semibold text-gray-700">{{ $totalDevices }}</p>
                </div>
            </div>
        </div>

        <!-- Stat Card: Incidents -->
        <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-red-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="mb-2 text-sm font-medium text-gray-600">Incidencias Activas</p>
                    <p class="text-2xl font-semibold text-gray-700">{{ $activeIncidents }}</p>
                </div>
            </div>
        </div>

        <!-- Stat Card: Alerts -->
        <div class="bg-white p-6 rounded-lg shadow-sm border-b-4 border-amber-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="mb-2 text-sm font-medium text-gray-600">Equipos en Alerta</p>
                    <p class="text-2xl font-semibold text-gray-700">{{ $alertDevices }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Actividad Reciente del Sistema</h3>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-gray-200">
                @forelse($recentEvents as $event)
                    <li class="py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="h-2 w-2 rounded-full {{ str_contains($event->type, 'desconex') ? 'bg-red-500' : 'bg-indigo-400' }} mr-3"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $event->type }}</p>
                                <p class="text-xs text-gray-500">{{ $event->device->name }} - {{ $event->device->location }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $event->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li class="py-10 text-center text-gray-400">Sin actividad reciente.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
