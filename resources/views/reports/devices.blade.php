<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventario de Dispositivos - Softlinkia</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; color: #4f46e5; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { bg-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .status-activo { color: #059669; font-weight: bold; }
        .status-alerta { color: #dc2626; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Inventario Maestro de Terminales</div>
        <div>Softlinkia S.A. de C.V. - Sistema de Monitoreo</div>
        <div style="margin-top: 5px;">Fecha de Reporte: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Ubicación</th>
                <th>Cliente</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devices as $device)
                <tr>
                    <td>{{ str_pad($device->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $device->name }}</td>
                    <td>{{ $device->type }}</td>
                    <td class="status-{{ $device->status }}">{{ strtoupper($device->status) }}</td>
                    <td>{{ $device->location }}</td>
                    <td>{{ $device->client->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este documento es confidencial y para uso exclusivo del personal autorizado de Softlinkia.
    </div>
</body>
</html>
