<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'type',
        'timestamp',
    ];

    /**
     * Lógica de Negocio Automatizada (Módulo 3)
     */
    protected static function booted()
    {
        static::created(function ($event) {
            $device = $event->device;
            $type = strtolower($event->type);

            // Regla: Si es desconexión -> Dispositivo Inactivo + Crear Incidencia
            if (str_contains($type, 'descon')) {
                $device->update(['status' => 'inactivo']);
                
                \App\Models\Incident::create([
                    'device_id' => $device->id,
                    'type' => 'Fallo Crítico',
                    'status' => 'pendiente',
                    'description' => "El sistema detectó una desconexión automática. El dispositivo ha sido marcado como inactivo.",
                ]);
            }

            // Regla: Si es anomalía o actividad sospechosa -> Dispositivo en Alerta + Crear Incidencia
            if (str_contains($type, 'anomal') || str_contains($type, 'sospechosa')) {
                $device->update(['status' => 'alerta']);
                
                \App\Models\Incident::create([
                    'device_id' => $device->id,
                    'type' => 'Alerta de Seguridad',
                    'status' => 'pendiente',
                    'description' => "Evento detectado: " . ucfirst($event->type) . ". Se requiere inspección del equipo.",
                ]);
            }
        });
    }

    /**
     * Get the device associated with this event.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
