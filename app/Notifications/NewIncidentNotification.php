<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewIncidentNotification extends Notification
{
    use Queueable;

    protected $incident;

    public function __construct(Incident $incident)
    {
        $incident->load('device');
        $this->incident = $incident;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'type' => $this->incident->type,
            'device_name' => $this->incident->device->name ?? 'Desconocido',
            'message' => "Nueva incidencia detectada: {$this->incident->type}",
            'url' => route('incidents.index'), // Ajustar según tu ruta real
        ];
    }
}
