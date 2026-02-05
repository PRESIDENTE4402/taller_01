<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BMW Service') }} - Restablecer Contraseña</title>
    
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
             <h1>Nueva Contraseña</h1>
        </div>

        <!-- Card -->
        <div class="login-card">
            
            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Form Group -->
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-envelope form-icon"></i>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="{{ old('email', $request->email) }}" required autofocus>
                    </div>
                    @error('email')
                        <div class="error-msg">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password Form Group -->
                <div class="form-group">
                    <label for="password" class="form-label">Nueva Contraseña</label>
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

                 <!-- Confirm Password Form Group -->
                 <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <div class="form-input-wrapper">
                        <i class="fas fa-lock form-icon"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" 
                               placeholder="••••••••••••" required>
                    </div>
                     @error('password_confirmation')
                        <div class="error-msg">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    RESTABLECER CONTRASEÑA <i class="fas fa-check"></i>
                </button>

            </form>

        </div>

    </div>

</body>
</html>
