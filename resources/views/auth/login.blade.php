@extends('layouts.guest')

@section('content')

    <!-- Header & Navigation -->
    <div class="mb-12">
        <!-- Back Link (Responsive positioning) -->
        <a href="{{ route('home') }}"
            class="inline-flex items-center text-gray-500 hover:text-white transition-colors mb-10 group text-sm font-medium">
            <div
                class="w-8 h-8 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center mr-3 group-hover:border-blue-600 transition-colors">
                <i class="fas fa-arrow-left text-xs transform group-hover:-translate-x-0.5 transition-transform"></i>
            </div>
            Volver al inicio
        </a>

        <!-- Mobile Logo -->
        <div class="lg:hidden flex justify-center mb-8">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/BMW.svg/2048px-BMW.svg.png" alt="BMW Logo"
                class="h-16 w-auto drop-shadow-lg">
        </div>

        <!-- Title Section -->
        <div class="space-y-2 text-center lg:text-left">
            <h2 class="text-4xl lg:text-5xl font-bold text-white tracking-tight">Bienvenido</h2>
            <p class="text-gray-400 text-lg">Accede a tu panel de control</p>
        </div>
    </div>

    <!-- Form -->
    <form class="space-y-8" action="{{ route('login') }}" method="POST" id="loginForm">
        @csrf

        <!-- Email -->
        <div class="group space-y-2">
            <label for="email"
                class="block text-sm font-medium text-gray-400 group-focus-within:text-blue-500 transition-colors uppercase tracking-wider">
                Correo Electrónico
            </label>
            <div class="relative">
                <input id="email" name="email" type="email" autocomplete="email" required
                    class="block w-full bg-gray-900 border border-gray-800 rounded-lg px-5 py-4 text-base text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all shadow-sm group-hover:border-gray-700"
                    placeholder="nombre@bmw.cl" value="{{ old('email') }}">
                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-700 group-focus-within:text-blue-500 transition-colors text-lg"></i>
                </div>
            </div>
            @error('email')
                <p class="mt-2 text-sm text-red-500 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Password -->
        <div class="group space-y-2">
            <div class="flex justify-between items-center">
                <label for="password"
                    class="block text-sm font-medium text-gray-400 group-focus-within:text-blue-500 transition-colors uppercase tracking-wider">
                    Contraseña
                </label>
            </div>
            <div class="relative">
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="block w-full bg-gray-900 border border-gray-800 rounded-lg px-5 py-4 text-base text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all shadow-sm group-hover:border-gray-700"
                    placeholder="••••••••">
                <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-700 group-focus-within:text-blue-500 transition-colors text-lg"></i>
                </div>
            </div>
            <div class="flex justify-end mt-1.5">
                <a href="#" class="text-sm text-gray-500 hover:text-white transition-colors">¿Olvidaste tu contraseña?</a>
            </div>
            @error('password')
                <p class="mt-2 text-sm text-red-500 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="pt-6 space-y-6">
            <!-- Checkbox -->
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                    class="h-5 w-5 rounded border-gray-700 bg-gray-900 text-blue-600 focus:ring-blue-500 focus:ring-offset-gray-900 cursor-pointer transition">
                <label for="remember"
                    class="ml-3 block text-base text-gray-400 cursor-pointer hover:text-white transition-colors select-none">
                    Mantener sesión iniciada
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full relative overflow-hidden flex justify-center items-center py-4 px-6 border border-transparent rounded-lg shadow-lg text-base font-bold text-white bg-blue-700 hover:bg-blue-600 focus:outline-none focus:ring-4 focus:ring-blue-900 transition-all transform hover:-translate-y-0.5 group">
                <span class="relative z-10 flex items-center gap-3 tracking-widest uppercase">
                    Iniciar Sesión <i
                        class="fas fa-arrow-right text-sm transform group-hover:translate-x-1 transition-transform"></i>
                </span>
                <div
                    class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-800 opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
            </button>
        </div>

        <!-- Register Link -->
        <div class="text-center mt-10">
            <p class="text-gray-500 text-sm">
                ¿Aún no tienes cuenta?
                <a href="#"
                    class="text-white font-semibold hover:text-blue-500 hover:underline transition-all ml-1">Regístrate
                    aquí</a>
            </p>
        </div>
    </form>

    @push('scripts')
        @vite('resources/js/auth/login.js')
    @endpush
@endsection