<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bitácora de Auditoría - Softlinkia</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1e293b; }
        .header { background: #0f172a; color: white; padding: 20px; text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .timestamp { color: #64748b; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 18px;">Registro Maestro de Auditoría</h1>
        <p style="margin: 5px 0 0 0; font-size: 10px; opacity: 0.8;">Softlinkia Security Infrastructure - SOC Report</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">Fecha / Hora</th>
                <th width="15%">Usuario</th>
                <th width="20%">Acción</th>
                <th width="40%">Descripción</th>
                <th width="10%">IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td class="timestamp">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->user->name ?? 'Sistema' }}</td>
                    <td style="font-weight: bold;">{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->ip_address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
