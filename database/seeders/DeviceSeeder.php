<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cliente = User::role('Cliente')->first();

        if ($cliente) {
            Device::create([
                'name' => 'Cámara Frontal 01',
                'type' => 'Cámara IP',
                'status' => 'activo',
                'location' => 'Entrada Principal',
                'metadata' => ['ip' => '192.168.1.50', 'firmware' => 'v2.4'],
                'client_id' => $cliente->id,
            ]);

            Device::create([
                'name' => 'Sensor Movimiento Pasillo',
                'type' => 'Sensor PIR',
                'status' => 'alerta',
                'location' => 'Pasillo Norte',
                'metadata' => ['sensibilidad' => 'alta'],
                'client_id' => $cliente->id,
            ]);

            Device::create([
                'name' => 'Alarma Incendio Cocina',
                'type' => 'Humo',
                'status' => 'inactivo',
                'location' => 'Cocina Industrial',
                'metadata' => ['bateria' => '15%'],
                'client_id' => $cliente->id,
            ]);
        }
    }
}
