<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
        }

        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            padding: 20px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #777;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            background-color: #dbeafe;
            color: #1e40af;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Notificación de Taller</h1>
        </div>
        <div class="content">
            @if(isset($body) && $body)
                <div style="white-space: pre-wrap;">{!! nl2br(e($body)) !!}</div>

                @if($tipo === 'cotizacion')
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}?mode=full"
                            class="button">Ver Cotización Completa</a>
                    </div>
                @elseif($tipo === 'fase')
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}?mode=avances"
                            class="button">Ver Detalles de Avance</a>
                    </div>
                @endif
            @else
                <p>Hola, <strong>{{ $orden->cliente->nombre_completo }}</strong>,</p>

                @if($tipo === 'listo')
                    <p>¡Buenas noticias! Los trabajos en tu vehículo <strong>{{ $orden->vehiculo->marca->nombre }}
                            {{ $orden->vehiculo->modelo->nombre }}</strong> (Placa: {{ $orden->vehiculo->placa }}) han
                        finalizado exitosamente.</p>
                    <p>Tu vehículo ya se encuentra listo para que pases a recogerlo en nuestra sucursal @if($orden->sucursal)
                    <strong>{{ $orden->sucursal->nombre }}</strong> @endif.
                    </p>
                @elseif($tipo === 'cotizacion')
                    <p>Te enviamos la cotización detallada de los servicios pendientes para tu vehículo
                        <strong>{{ $orden->vehiculo->marca->nombre }} {{ $orden->vehiculo->modelo->modelo }}</strong> (Placa:
                        {{ $orden->vehiculo->placa }}).</p>
                    <p>Por favor, revisa el detalle a continuación y confírmanos para proceder con los trabajos.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}?mode=full"
                            class="button">Ver Cotización Completa</a>
                    </div>
                @else
                    <p>Te informamos que tu vehículo <strong>{{ $orden->vehiculo->marca->nombre }}
                            {{ $orden->vehiculo->modelo->nombre }}</strong> (Placa: {{ $orden->vehiculo->placa }}) ha cambiado
                        de fase en nuestro taller:</p>
                    <p style="text-align: center;">
                        <span class="status-badge">{{ str_replace('_', ' ', $orden->estado) }}</span>
                    </p>
                    <p>Seguimos trabajando para entregarte los mejores resultados en el menor tiempo posible.</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ route('panel.operaciones.ordenes_trabajo.print', $orden->id) }}?mode=avances"
                            class="button">Ver Detalles de Avance</a>
                    </div>
                @endif
            @endif

            <p><strong>Detalle de la Orden:</strong> #{{ $orden->codigo_orden }}</p>

            <div style="text-align: center; margin-top: 30px;">
                <p>Gracias por confiar en <strong>Tecnimecánica California</strong>.</p>
            </div>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este correo.</p>
            <p>&copy; {{ date('Y') }} Tecnimecánica California</p>
        </div>
    </div>
</body>

</html>