<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Incidencias de Seguridad - Softlinkia</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        .header { border-left: 5px solid #e11d48; padding-left: 15px; margin-bottom: 30px; }
        .title { font-size: 20px; font-weight: bold; color: #e11d48; }
        .grid { display: block; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 12px 8px; text-align: left; }
        th { background: #f8fafc; color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .badge-pendiente { background: #fee2e2; color: #991b1b; }
        .badge-proceso { background: #fef3c7; color: #92400e; }
        .badge-resuelto { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Bitácora Técnica de Incidencias</div>
        <div style="font-size: 13px; font-weight: bold; margin-top: 5px;">Softlinkia Security Operations Center (SOC)</div>
        <div style="color: #64748b; margin-top: 3px;">Reporte generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ticker</th>
                <th>Dispositivo / Cliente</th>
                <th>Alerta</th>
                <th>Estado</th>
                <th>Asignado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($incidents as $incident)
                <tr>
                    <td style="font-weight: bold;">#{{ str_pad($incident->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $incident->device->name }}</div>
                        <div style="color: #64748b; font-size: 9px;">{{ $incident->device->client->name ?? 'Externo' }}</div>
                    </td>
                    <td>{{ $incident->type }}</td>
                    <td>
                        <span class="badge badge-{{ $incident->status === 'en proceso' ? 'proceso' : $incident->status }}">
                            {{ $incident->status }}
                        </span>
                    </td>
                    <td>{{ $incident->assignedUser->name ?? 'Sin asignar' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; color: #94a3b8; font-size: 9px;">
        Este reporte es generado automáticamente por el sistema de auditoría de Softlinkia. Prohibida su reproducción total o parcial sin autorización.
    </div>
</body>
</html>
