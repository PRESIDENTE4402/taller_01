<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #0F1012;
            margin: 0;
            padding: 0;
            color: #ffffff;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #1A1B1E;
            border-top: 4px solid #1C69D4;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        .header {
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #2E2E2E;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content h2 {
            color: #1C69D4;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            color: #cccccc;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #1C69D4;
            color: #ffffff;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1458b8;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
            border-top: 1px solid #2E2E2E;
        }
        .sub-copy {
            font-size: 12px;
            color: #888888;
            margin-top: 30px;
            text-align: left;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- You can add the logo here if you want -->
            <h1>BMW Service</h1>
        </div>
        
        <div class="content">
            <h2>Solicitud de Restablecimiento</h2>
            <p>Hola,</p>
            <p>Recibiste este correo porque se solicitó un restablecimiento de contraseña para tu cuenta.</p>
            
            <a href="{{ $url }}" class="btn" style="display: inline-block; background-color: #1C69D4; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 5px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 20px 0;">Restablecer Contraseña</a>
            
            <p>Este enlace de restablecimiento expirará en 60 minutos.</p>
            <p>Si no realizaste esta solicitud, no se requiere ninguna otra acción.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} BMW Service. Todos los derechos reservados.
            
            <div class="sub-copy">
                <p>Si tienes problemas para hacer clic en el botón "Restablecer Contraseña", copia y pega la siguiente URL en tu navegador web: <br>
                <a href="{{ $url }}" style="color: #1C69D4;">{{ $url }}</a></p>
            </div>
        </div>
    </div>
</body>
</html>
