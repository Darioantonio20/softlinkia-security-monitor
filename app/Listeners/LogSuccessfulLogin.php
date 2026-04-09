<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        AuditLog::create([
            'user_id' => $event->user->id,
            'action' => 'LOGIN',
            'description' => "El usuario {$event->user->name} ha iniciado sesión en el sistema.",
            'ip_address' => Request::ip(),
            'metadata' => [
                'user_agent' => Request::header('User-Agent'),
            ]
        ]);
    }
}
