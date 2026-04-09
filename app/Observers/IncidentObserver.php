<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\IncidentHistory;
use App\Models\User;
use App\Notifications\NewIncidentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class IncidentObserver
{
    /**
     * Handle the Incident "updated" event.
     */
    public function updated(Incident $incident): void
    {
        if ($incident->isDirty('status')) {
            IncidentHistory::create([
                'incident_id' => $incident->id,
                'status_before' => $incident->getOriginal('status'),
                'status_after' => $incident->status,
                'user_id' => Auth::id() ?? $incident->assigned_user_id,
                'comments' => 'Cambio de estado automático registrado por el sistema.',
            ]);

            // Auditoría adicional
            \App\Models\AuditLog::create([
                'action' => 'CAMBIO_ESTADO_INCIDENCIA',
                'description' => "Incidencia #{$incident->id} cambió de {$incident->getOriginal('status')} a {$incident->status}",
                'ip_address' => request()->ip(),
                'user_id' => Auth::id(),
                'metadata' => [
                    'incident_id' => $incident->id,
                    'old_status' => $incident->getOriginal('status'),
                    'new_status' => $incident->status
                ]
            ]);
        }
    }

    /**
     * Handle the Incident "created" event.
     */
    public function created(Incident $incident): void
    {
        IncidentHistory::create([
            'incident_id' => $incident->id,
            'status_before' => 'nuevo',
            'status_after' => $incident->status,
            'user_id' => Auth::id() ?? $incident->assigned_user_id,
            'comments' => 'Creación inicial de la incidencia.',
        ]);

        // Notificar a administradores y operadores
        $usersToNotify = User::role(['Administrador', 'Operador'])->get();
        Notification::send($usersToNotify, new NewIncidentNotification($incident));
    }
}
