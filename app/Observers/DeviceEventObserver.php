<?php

namespace App\Observers;

use App\Models\DeviceEvent;
use App\Models\Incident;
use Illuminate\Support\Facades\Log;

class DeviceEventObserver
{
    /**
     * Handle the DeviceEvent "created" event.
     */
    public function created(DeviceEvent $deviceEvent): void
    {
        // Detectar desconexión de forma robusta
        if (str_contains(strtolower($deviceEvent->type), 'desconex')) {
            try {
                Incident::create([
                    'device_id' => $deviceEvent->device_id,
                    'type' => 'desconexión detectada',
                    'status' => 'pendiente',
                    'description' => 'El dispositivo reportó una desconexión automática a las ' . $deviceEvent->timestamp,
                ]);

                \App\Models\AuditLog::create([
                    'action' => 'EVENTO_CRITICO',
                    'description' => "Sistema detectó desconexión en dispositivo ID: {$deviceEvent->device_id}",
                    'ip_address' => request()->ip(),
                    'metadata' => ['device_id' => $deviceEvent->device_id]
                ]);

                Log::info("Incidencia automática creada para el dispositivo ID: {$deviceEvent->device_id}");
            } catch (\Exception $e) {
                Log::error("Error al crear incidencia automática: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle the DeviceEvent "updated" event.
     */
    public function updated(DeviceEvent $deviceEvent): void
    {
        //
    }

    /**
     * Handle the DeviceEvent "deleted" event.
     */
    public function deleted(DeviceEvent $deviceEvent): void
    {
        //
    }

    /**
     * Handle the DeviceEvent "restored" event.
     */
    public function restored(DeviceEvent $deviceEvent): void
    {
        //
    }

    /**
     * Handle the DeviceEvent "force deleted" event.
     */
    public function forceDeleted(DeviceEvent $deviceEvent): void
    {
        //
    }
}
