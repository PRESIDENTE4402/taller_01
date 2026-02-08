<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background-color: #f7fafc; padding: 20px; }
        .container { background-color: white; border-radius: 8px; padding: 30px; margin: 0 auto; max-width: 600px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; border-bottom: 2px solid #edf2f7; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #2d3748; font-size: 24px; margin: 0; }
        .content { color: #4a5568; line-height: 1.6; }
        .details { background-color: #ebf8ff; border-radius: 6px; padding: 20px; margin: 20px 0; border: 1px solid #bee3f8; }
        .details p { margin: 5px 0; color: #2c5282; font-weight: bold; }
        .footer { text-align: center; color: #718096; font-size: 12px; margin-top: 30px; border-top: 1px solid #edf2f7; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Hola {{ $cita->cliente->nombre }}!</h1>
        </div>
        
        <div class="content">
            <p>Le escribimos de <strong>TallerPro</strong> para recordarle su cita programada para el día de mañana.</p>
            
            <div class="details">
                <p>📅 Fecha: {{ \Carbon\Carbon::parse($cita->fecha_programada)->translatedFormat('l j \d\e F \d\e Y') }}</p>
                <p>⏰ Hora: {{ \Carbon\Carbon::parse($cita->fecha_programada)->format('h:i A') }}</p>
                <p>🚗 Vehículo: {{ $cita->vehiculo->marca->nombre ?? '' }} {{ $cita->vehiculo->modelo->nombre ?? '' }} ({{ $cita->vehiculo->placa ?? '' }})</p>
                <p>🔧 Motivo: {{ $cita->motivo_cita }}</p>
            </div>

            <p>Agradecemos su puntualidad. Si necesita reprogramar o tiene alguna duda, por favor contáctenos.</p>
            
            <p>Un cordial saludo,<br>El equipo de TallerPro.</p>
        </div>

        <div class="footer">
            <p>Este es un mensaje automático, por favor no responda a este correo.</p>
        </div>
    </div>
</body>
</html>
