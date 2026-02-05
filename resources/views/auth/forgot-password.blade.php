<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BMW Service') }} - Recuperar Contraseña</title>
    
    <!-- External Icons (FontAwesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Custom CSS -->
    <style>
        @include('auth.login-css') 
    </style>
</head>
<body class="login-page">

    <!-- Background -->
    <div class="login-background"></div>

    <!-- Login Container -->
    <div class="login-container">
        
        <!-- Branding -->
        <div class="login-brand">
             <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/BMW.svg/2048px-BMW.svg.png" 
                  alt="BMW Logo">
             <h1>Recuperar Cuenta</h1>
        </div>

        <!-- Card -->
        <div class="login-card">
            
            <div style="margin-bottom: 20px; color: var(--text-muted); font-size: 14px; text-align: center;">
                ¿Olvidaste tu contraseña? No hay problema. Simplemente haznos saber tu dirección de correo electrónico y te enviaremos un enlace para restablecerla.
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div style="color: #4caf50; font-size: 14px; margin-bottom: 20px; text-align: center;">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <!-- Email Form Group -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" id="email" name="email" class="form-input" 
                               placeholder="ej. usuario@bmw.cl" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <div class="error-msg">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    ENVIAR ENLACE <i class="fas fa-paper-plane"></i>
                </button>

            </form>

        </div>

        <!-- Back Link -->
        <div style="text-align: center;">
            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
            </a>
        </div>

    </div>

</body>
</html>
