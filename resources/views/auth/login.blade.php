<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BMW Service') }} - Acceso</title>
    
    <!-- External Icons (FontAwesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Custom CSS (Injected directly to avoid Vite conflicts during this switch) -->
    <!-- Custom CSS (Injected directly to avoid Vite conflicts during this switch) -->
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
             <h1>BMW ConnectedDrive</h1>
        </div>

        <!-- Card -->
        <div class="login-card">
            
            <form action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Email Form Group -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" id="email" name="email" class="form-input" 
                               placeholder="ej. usuario@bmw.cl" value="{{ old('email') }}" required>
                    </div>
                    @error('email')
                        <div class="error-msg">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password Form Group -->
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="••••••••••••" required>
                    </div>
                     @error('password')
                        <div class="error-msg">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="remember">
                        <span class="checkbox-label">Recordar mi sesión</span>
                    </label>
                    <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    INGRESAR <i class="fas fa-arrow-right"></i>
                </button>

            </form>

            <!-- Footer Links -->
            <div class="login-footer">
                ¿Aún no tienes cuenta? <a href="#">Registrar vehículo</a>
            </div>

        </div>

        <!-- Back Link -->
        <div style="text-align: center;">
            <a href="{{ route('home') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al inicio
            </a>
        </div>

    </div>

</body>
</html>