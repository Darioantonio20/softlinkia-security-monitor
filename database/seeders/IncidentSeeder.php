<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Seeder;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        $devices = Device::all();
        $operador = User::role('Operador')->first();
        
        $types = ['Desconexión', 'Sabotaje', 'Detección de Humo', 'Fallo de Red', 'Movimiento Sospechoso'];
        $statuses = ['pendiente', 'en proceso', 'resuelto'];
        $descriptions = [
            'El dispositivo perdió comunicación con el servidor central.',
            'Se detectó un intento de apertura física del chasis.',
            'Presencia de partículas en aire por encima del umbral permitido.',
            'Latencia alta detectada en la interfaz de red.',
            'Patrón de movimiento no identificado en horario no laboral.'
        ];

        // Crear 25 incidencias aleatorias si la tabla está vacía
        if (Incident::count() >= 25) return;

        foreach (range(1, 25) as $index) {
            $device = $devices->random();
            $status = $statuses[array_rand($statuses)];
            
            Incident::create([
                'device_id' => $device->id,
                'type' => $types[array_rand($types)],
                'status' => $status,
                'description' => $descriptions[array_rand($descriptions)],
                'assigned_user_id' => ($status !== 'pendiente' && $operador) ? $operador->id : null,
                'created_at' => now()->subDays(rand(0, 15))->subHours(rand(0, 23)),
            ]);
        }
    }
}
