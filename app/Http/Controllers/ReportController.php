<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Incident;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Exportar Dispositivos a PDF
     */
    public function exportDevicesPDF()
    {
        $devices = Device::with('client')->get();
        $pdf = Pdf::loadView('reports.devices', compact('devices'));
        
        return $pdf->download('reporte-dispositivos-softlinkia.pdf');
    }

    /**
     * Exportar Dispositivos a CSV
     */
    public function exportDevicesCSV()
    {
        $fileName = 'dispositivos-softlinkia.csv';
        $devices = Device::with('client')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Nombre', 'Tipo', 'Estado', 'Ubicación', 'Cliente'];

        $callback = function() use($devices, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($devices as $device) {
                fputcsv($file, [
                    $device->id,
                    $device->name,
                    $device->type,
                    $device->status,
                    $device->location,
                    $device->client->name ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar Incidencias a PDF
     */
    public function exportIncidentsPDF()
    {
        $incidents = Incident::with(['device', 'assignedUser', 'device.client'])->get();
        $pdf = Pdf::loadView('reports.incidents', compact('incidents'));
        
        return $pdf->download('reporte-incidencias-tecnico.pdf');
    }

    /**
     * Exportar Bitácora de Auditoría a PDF
     */
    public function exportAuditPDF()
    {
        $logs = \App\Models\AuditLog::with('user')->latest()->get();
        $pdf = Pdf::loadView('reports.audit', compact('logs'));
        
        return $pdf->download('bitacora-auditoria-softlinkia.pdf');
    }

    /**
     * Exportar Bitácora de Auditoría a CSV
     */
    public function exportAuditCSV()
    {
        $fileName = 'bitacora-auditoria.csv';
        $logs = \App\Models\AuditLog::with('user')->latest()->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $callback = function() use($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Fecha', 'Usuario', 'Acción', 'Descripción', 'IP']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'Sistema',
                    $log->action,
                    $log->description,
                    $log->ip_address,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
