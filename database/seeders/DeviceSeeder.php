<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = User::role('Cliente')->first();
        if (!$cliente) return;

        $types = ['Cámara IP', 'Sensor PIR', 'Humo', 'Control de Acceso', 'Sensor Sísmico'];
        $locations = ['Entrada Principal', 'Pasillo Norte', 'Cocina Industrial', 'Estacionamiento B', 'Sala de Servidores', 'Alba C', 'Puerta Perimetral'];
        $statuses = ['activo', 'alerta', 'inactivo'];

        // Crear 40 dispositivos aleatorios
        for ($i = 1; $i <= 40; $i++) {
            $type = $types[array_rand($types)];
            Device::updateOrCreate(
                ['name' => "$type " . str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'type' => $type,
                    'status' => $statuses[array_rand($statuses)],
                    'location' => $locations[array_rand($locations)],
                    'metadata' => [
                        'ip' => '192.168.1.' . (50 + $i),
                        'firmware' => 'v' . rand(1, 3) . '.' . rand(0, 9),
                        'uptime' => rand(90, 100) . '%',
                    ],
                    'client_id' => $cliente->id,
                ]
            );
        }
    }
}
