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

        // Datos para la gráfica (últimos 7 días)
        $days = collect(range(0, 6))->map(fn($i) => now()->subDays($i)->format('Y-m-d'))->reverse();
        $incidentCounts = $days->map(fn($date) => 
            Incident::whereDate('created_at', $date)->count()
        );

        return [
            'totalDevices' => $totalDevices,
            'activeIncidents' => $incidentsQuery->count(),
            'alertDevices' => $alertDevicesCount,
            'recentEvents' => $eventsQuery->latest()->take(10)->with(['device.client'])->get(),
            'clients' => User::role('Cliente')->get(),
            'deviceTypes' => Device::distinct()->pluck('type'),
            'chartData' => [
                'labels' => $days->map(fn($d) => date('d M', strtotime($d)))->values(),
                'values' => $incidentCounts->values(),
            ],
            'stats' => [
                'active' => $activeDevices,
                'alert' => $alertDevicesCount,
                'inactive' => $inactiveDevices,
                'health' => $healthRate
            ]
        ];
    }
}; ?>

<div wire:poll.30s>

    {{-- ════ PAGE HEADER ════════════════════════════════════════ --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 mb-12">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-2">Centro de Operaciones · Softlinkia Security</p>
            <h1 class="text-[40px] font-black text-slate-900 tracking-[-0.03em] leading-none">Resumen General</h1>
        </div>

        {{-- Filtros --}}
        <div class="flex items-center gap-3 flex-wrap">
            @unless(auth()->user()->hasRole('Cliente'))
            <div class="relative">
                <select wire:model.live="clientFilter"
                        class="appearance-none bg-white border border-slate-200 text-slate-700 text-[11px] font-bold rounded-2xl pl-4 pr-9 py-2.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Todos los clientes</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </div>
            @endunless

            <div class="relative">
                <select wire:model.live="typeFilter"
                        class="appearance-none bg-white border border-slate-200 text-slate-700 text-[11px] font-bold rounded-2xl pl-4 pr-9 py-2.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                    <option value="">Todos los tipos</option>
                    @foreach($deviceTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
            </div>

            @if($clientFilter || $typeFilter)
            <button wire:click="$set('clientFilter', ''); $set('typeFilter', '')"
                    class="flex items-center gap-1.5 px-3 py-2.5 text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-100 rounded-2xl hover:bg-rose-100 transition-colors">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                Limpiar
            </button>
            @endif
        </div>
    </div>

    {{-- ════ KPI CARDS ════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-10">

        {{-- Salud del sistema --}}
        <div class="col-span-2 lg:col-span-1 relative overflow-hidden bg-white border border-slate-200/60 rounded-[2.5rem] p-7 shadow-sm group transition-all duration-700">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50/50 rounded-full blur-3xl group-hover:bg-indigo-100/50 transition-colors duration-500"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-7">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Salud Global</p>
                    <div class="flex items-center gap-2 px-2 py-1 bg-emerald-50 rounded-lg">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-[9px] font-bold text-emerald-600 uppercase">Live</span>
                    </div>
                </div>

                <div class="flex items-center gap-6 mb-2">
                    <div class="relative flex items-center justify-center w-24 h-24">
                        <svg class="w-full h-full transform -rotate-90">
                            <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-50"/>
                            <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" 
                                    stroke-dasharray="251.2" 
                                    stroke-dashoffset="{{ 251.2 - (251.2 * $stats['health']) / 100 }}" 
                                    class="text-indigo-500 transition-all duration-1000 ease-out" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute flex flex-col items-center">
                            <span class="text-2xl font-black text-slate-900 tracking-tighter">{{ $stats['health'] }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-[22px] font-black text-slate-900 leading-tight mb-1">
                            {{ $stats['health'] > 80 ? 'Óptima' : ($stats['health'] > 50 ? 'Regular' : 'Crítica') }}
                        </p>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-tighter">
                            {{ $stats['active'] }} de {{ $totalDevices }} <br>activos ahora
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dispositivos --}}
        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-7 shadow-sm transition-all duration-700 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 transition-all duration-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Flota Total</p>
                    <p class="text-3xl font-black text-slate-900 mt-1">{{ $totalDevices }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4">
                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ $totalDevices > 0 ? ($stats['active'] / $totalDevices) * 100 : 0 }}%"></div>
                </div>
                <span class="text-[10px] font-black text-blue-600">{{ $stats['active'] }} ON</span>
            </div>
        </div>

        {{-- Incidencias --}}
        <div class="bg-white border {{ $activeIncidents > 0 ? 'border-rose-200 shadow-[0_15px_30px_rgba(244,63,94,0.08)]' : 'border-slate-100 shadow-[0_10px_30px_rgba(0,0,0,0.02)]' }} rounded-[2.5rem] p-7 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 {{ $activeIncidents > 0 ? 'bg-rose-100 text-rose-600 shadow-[0_8px_20px_rgba(244,63,94,0.2)]' : 'bg-slate-50 text-slate-400' }} rounded-2xl flex items-center justify-center transition-all duration-500">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Incidencias</p>
                    <p class="text-3xl font-black {{ $activeIncidents > 0 ? 'text-rose-600' : 'text-slate-900' }} mt-1">{{ $activeIncidents }}</p>
                </div>
            </div>
            <div class="py-1">
                <span class="text-[10px] font-black {{ $activeIncidents > 0 ? 'text-rose-600' : 'text-slate-400' }} uppercase tracking-widest">
                    {{ $activeIncidents > 0 ? 'Requiere atención inmediata' : 'Sin alertas críticas' }}
                </span>
            </div>
        </div>

        {{-- En alerta --}}
        <div class="bg-white border border-slate-100 rounded-[2.5rem] p-7 shadow-sm transition-all duration-700 group">
            <div class="flex items-center justify-between mb-6">
                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 transition-all duration-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">En Alerta</p>
                    <p class="text-3xl font-black text-slate-900 mt-1">{{ $alertDevices }}</p>
                </div>
            </div>
            <div class="py-1">
                <span class="text-[10px] font-black {{ $alertDevices > 0 ? 'text-amber-600' : 'text-slate-400' }} uppercase tracking-widest">
                    {{ $alertDevices > 0 ? 'Anomalías detectadas' : 'Estado de red óptimo' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ════ MAIN GRID ════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Panel izquierdo: Distribución --}}
        <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm">

            <div class="mb-9">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-0.5">Distribución</p>
                <p class="text-base font-black text-slate-900">Estado de la Flota</p>
            </div>

            @php
                $actPercent = $totalDevices > 0 ? ($stats['active'] / $totalDevices) * 100 : 0;
                $alePercent = $totalDevices > 0 ? ($stats['alert'] / $totalDevices) * 100 : 0;
                $inaPercent = $totalDevices > 0 ? ($stats['inactive'] / $totalDevices) * 100 : 0;
            @endphp

            <div class="space-y-8">
                {{-- Operativos --}}
                <div>
                    <div class="flex justify-between items-center mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                            <span class="text-[12px] font-black text-slate-700">Operativos</span>
                        </div>
                        <span class="text-[13px] font-black text-slate-900">{{ $stats['active'] }}<span class="text-slate-400 font-bold text-[10px] ml-1">/ {{ $totalDevices }}</span></span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-700" style="width: {{ $actPercent }}%"></div>
                    </div>
                </div>

                {{-- Alertas --}}
                <div>
                    <div class="flex justify-between items-center mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                            <span class="text-[12px] font-black text-slate-700">En Alerta</span>
                        </div>
                        <span class="text-[13px] font-black text-slate-900">{{ $stats['alert'] }}<span class="text-slate-400 font-bold text-[10px] ml-1">/ {{ $totalDevices }}</span></span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-rose-500 rounded-full transition-all duration-700" style="width: {{ $alePercent }}%"></div>
                    </div>
                </div>

                {{-- Inactivos --}}
                <div>
                    <div class="flex justify-between items-center mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                            <span class="text-[12px] font-black text-slate-700">Inactivos</span>
                        </div>
                        <span class="text-[13px] font-black text-slate-900">{{ $stats['inactive'] }}<span class="text-slate-400 font-bold text-[10px] ml-1">/ {{ $totalDevices }}</span></span>
                    </div>
                    <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-slate-300 rounded-full transition-all duration-700" style="width: {{ $inaPercent }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Métricas secundarias --}}
            <div class="grid grid-cols-2 gap-4 mt-10 pt-8 border-t border-slate-100">
                <div class="bg-slate-50 rounded-2xl p-4 text-center">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Cobertura</p>
                    <p class="text-xl font-black text-slate-900">{{ $stats['health'] }}<span class="text-sm text-slate-400">%</span></p>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 text-center">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Uptime</p>
                    <p class="text-xl font-black text-slate-900">99<span class="text-sm text-slate-400">.8%</span></p>
                </div>
            </div>
        </div>

        {{-- Panel Central: Gráfica de Tendencias --}}
        <div class="bg-white border border-slate-100 rounded-[2rem] p-8 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-0.5">Analítica</p>
                <p class="text-base font-black text-slate-900 mb-6">Tendencia de Incidentes (7d)</p>
            </div>
            
            <div class="flex-1 min-h-[200px] relative">
                <canvas id="incidentChart"></canvas>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-50">
                <p class="text-[10px] text-slate-400 font-medium">Actualizado en tiempo real vía <span class="text-indigo-600 font-bold">Softlinkia Core</span></p>
            </div>
        </div>

        {{-- Panel derecho: Actividad --}}
        <div class="lg:col-span-2 bg-white border border-slate-100 rounded-[2rem] shadow-sm overflow-hidden flex flex-col">

            {{-- Header --}}
            <div class="flex items-center justify-between px-8 py-5 border-b border-slate-100">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.35em] mb-0.5">En tiempo real</p>
                    <p class="text-base font-black text-slate-900">Actividad Reciente</p>
                </div>
                <a href="{{ route('incidents') }}"
                   class="flex items-center gap-2 text-[11px] font-black text-indigo-600 uppercase tracking-widest hover:gap-3 transition-all duration-500">
                    Ver incidencias
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            {{-- Lista --}}
            <div class="flex-1 divide-y divide-slate-50 overflow-y-auto max-h-[480px]">
                @forelse($recentEvents as $event)
                    @php
                        $isCritical = str_contains(strtolower($event->type), 'descon') || str_contains(strtolower($event->type), 'critical');
                    @endphp
                    <div class="flex items-center gap-5 px-8 py-5 hover:bg-slate-50/70 transition-colors {{ $isCritical ? 'bg-rose-50/40' : '' }}">

                        {{-- Icono --}}
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center {{ $isCritical ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }}">
                            @if($isCritical)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-[13px] font-black text-slate-900 truncate">{{ $event->device->name }}</span>
                                <span class="flex-shrink-0 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $isCritical ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $event->type }}
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium truncate">
                                {{ $event->device->location }}
                                @if($event->device->client)
                                    &middot; {{ $event->device->client->name }}
                                @endif
                            </p>
                        </div>

                        {{-- Timestamp --}}
                        <div class="flex-shrink-0 text-right ml-2">
                            <p class="text-[12px] font-black text-slate-700 tabular-nums leading-none mb-0.5">{{ $event->created_at->format('H:i') }}</p>
                            <p class="text-[10px] text-slate-400">{{ $event->created_at->format('d M') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-24 px-8 text-center">
                        <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-200 mb-4 border border-slate-100">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Sin actividad registrada</p>
                        <p class="text-sm text-slate-400">Los eventos aparecerán aquí automáticamente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        initChart();
    });

    document.addEventListener('livewire:load', () => {
        initChart();
    });

    function initChart() {
        const ctx = document.getElementById('incidentChart');
        if (!ctx) return;

        let chartStatus = Chart.getChart("incidentChart");
        if (chartStatus != undefined) {
            chartStatus.destroy();
        }

        const labels = @js($chartData['labels']);
        const values = @js($chartData['values']);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Incidentes',
                    data: values,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 4,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: false },
                        ticks: { stepSize: 1, font: { size: 9, weight: 'bold' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9, weight: 'bold' } }
                    }
                }
            }
        });
    }

    setTimeout(initChart, 500);
</script>
</div>
