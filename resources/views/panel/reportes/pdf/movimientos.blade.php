<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos de Inventario</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e293b; font-size: 18px; }
        .info { margin-bottom: 15px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th { background-color: #f8fafc; color: #64748b; font-weight: bold; text-transform: uppercase; padding: 8px; border: 1px solid #e2e8f0; font-size: 8px; }
        .table td { padding: 8px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .text-emerald { color: #059669; font-weight: bold; }
        .text-amber { color: #d97706; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 8px; color: #94a3b8; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 7px; text-transform: uppercase; }
        .bg-emerald { background-color: #ecfdf5; color: #065f46; }
        .bg-amber { background-color: #fffbeb; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KARDEX DE INVENTARIO</h1>
        <p>Reporte de Movimientos Detallados</p>
    </div>

    <div class="info">
        <table style="width: 100%;">
            <tr>
                <td><strong>Periodo:</strong> {{ $fechaInicio }} al {{ $fechaFin }}</td>
                <td style="text-align: right;"><strong>Generado el:</strong> {{ $fechaGeneracion }}</td>
            </tr>
            <tr>
                <td><strong>Sucursal:</strong> {{ $sucursal }}</td>
                <td></td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Producto / SKU</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Stock Ant.</th>
                <th>Stock Nuevo</th>
                <th>Motivo</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientos as $m)
                <tr>
                    <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <strong>{{ $m->repuesto->nombre }}</strong><br>
                        <small>{{ $m->repuesto->codigo_interno }}</small>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge {{ $m->tipo === 'entrada' ? 'bg-emerald' : 'bg-amber' }}">
                            {{ $m->tipo }}
                        </span>
                    </td>
                    <td style="text-align: right;" class="{{ $m->tipo === 'entrada' ? 'text-emerald' : 'text-amber' }}">
                        {{ $m->tipo === 'entrada' ? '+' : '-' }}{{ number_format($m->cantidad, 2) }}
                    </td>
                    <td style="text-align: right;">{{ number_format($m->stock_anterior, 2) }}</td>
                    <td style="text-align: right;"><strong>{{ number_format($m->stock_nuevo, 2) }}</strong></td>
                    <td>
                        <small>{{ str_replace(['_', '-'], ' ', $m->motivo) }}</small>
                        @if($m->notas)
                            <br><small style="color: #94a3b8; font-style: italic;">{{ $m->notas }}</small>
                        @endif
                    </td>
                    <td>{{ $m->usuario->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        {{ config('app.name') }} - Sistema de Gestión de Taller
    </div>
</body>
</html>
