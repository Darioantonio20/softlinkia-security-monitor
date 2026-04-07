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
            $query->where('action', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
        }

        return [
            'logs' => $query->latest()->paginate(20),
        ];
    }
}; ?>

<div>
    <div class="mb-6">
        <input wire:model.live="search" type="text" placeholder="Filtrar por usuario o acción..." 
               class="w-full md:w-1/2 border-gray-300 rounded-md shadow-sm focus:ring-gray-500">
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Acción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Descripción</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $log->user->name ?? 'Sistema' }}</div>
                            <div class="text-xs text-gray-500">{{ $log->user->email ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-bold rounded bg-gray-100 text-gray-700">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $log->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No hay registros en la bitácora.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
