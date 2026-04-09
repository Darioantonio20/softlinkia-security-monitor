<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registramos si el usuario está autenticado y la petición fue exitosa (200-399)
        // Y solo para métodos que alteran datos (POST, PUT, PATCH, DELETE)
        if (Auth::check() && $response->getStatusCode() < 400 && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            
            $action = 'ACCION_SISTEMA';
            $description = "Petición {$request->method()} a la ruta: " . $request->path();
            
            // Intentamos darle un nombre más amigable según la ruta
            if (str_contains($request->path(), 'devices')) $action = 'GESTION_DISPOSITIVOS';
            if (str_contains($request->path(), 'incidents')) $action = 'GESTION_INCIDENCIAS';
            if (str_contains($request->path(), 'simulate-event')) $action = 'SIMULACION_API';

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => $request->ip(),
                'metadata' => [
                    'payload' => $request->except(['password', 'password_confirmation', '_token']),
                    'status_code' => $response->getStatusCode()
                ]
            ]);
        }

        return $response;
    }
}
