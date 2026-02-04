@extends('layouts.guest')

@section('content')
    <div class="mb-12">
        <a href="{{ route('home') }}"
            class="inline-flex items-center text-gray-500 hover:text-white transition-all group text-[10px] font-bold uppercase tracking-[0.3em]">
            <div
                class="w-10 h-10 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center mr-4 group-hover:border-blue-600 transition-all">
                <i class="fas fa-arrow-left transform group-hover:-translate-x-1 transition-transform"></i>
            </div>
            Volver al inicio
        </a>
    </div>

    <div class="w-full bg-[#0d0d0d] border border-gray-800/60 p-8 lg:p-12 rounded-3xl shadow-2xl relative">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-[1px] bg-blue-600 shadow-[0_0_15px_rgba(37,99,235,1)]">
        </div>

        <div class="mb-10">
            <h2 class="text-3xl font-bold text-white tracking-tight mb-3">Bienvenido</h2>
            <p class="text-gray-500 text-sm leading-relaxed">
                Ingresa tus credenciales para acceder al panel.
            </p>
        </div>

        <form class="space-y-6" action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-600 uppercase tracking-[0.2em] ml-1">Correo
                    Electrónico</label>
                <div class="relative group">
                    <input id="email" name="email" type="email" required
                        class="w-full h-14 bg-black border border-gray-800 rounded-xl px-6 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                        placeholder="nombre@bmw.cl">
                    <i
                        class="fas fa-envelope absolute right-6 top-1/2 -translate-y-1/2 text-gray-800 group-focus-within:text-blue-500"></i>
                </div>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between items-center px-1">
                    <label class="text-[10px] font-bold text-gray-600 uppercase tracking-[0.2em]">Contraseña</label>
                    <a href="#" class="text-[9px] text-blue-500 hover:text-blue-400 font-bold uppercase">¿Olvidaste la
                        clave?</a>
                </div>
                <div class="relative group">
                    <input id="password" name="password" type="password" required
                        class="w-full h-14 bg-black border border-gray-800 rounded-xl px-6 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                        placeholder="••••••••">
                    <i
                        class="fas fa-lock absolute right-6 top-1/2 -translate-y-1/2 text-gray-800 group-focus-within:text-blue-500"></i>
                </div>
            </div>

            <div class="pt-2 space-y-6">
                <label class="flex items-center cursor-pointer group w-fit">
                    <input type="checkbox" class="peer hidden">
                    <div
                        class="w-5 h-5 border border-gray-800 bg-black rounded flex items-center justify-center peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all">
                        <i class="fas fa-check text-[10px] text-white opacity-0 peer-checked:opacity-100"></i>
                    </div>
                    <span class="ml-3 text-xs text-gray-500 group-hover:text-gray-300">Recordar sesión</span>
                </label>

                <button type="submit"
                    class="w-full h-14 bg-blue-600 hover:bg-blue-500 text-white font-black tracking-[0.2em] uppercase text-xs rounded-xl shadow-lg transition-all active:scale-[0.98]">
                    INICIAR SESIÓN
                </button>
            </div>

            <div class="text-center pt-4">
                <p class="text-gray-600 text-[10px] font-bold uppercase tracking-[0.1em]">
                    ¿No tienes cuenta? <a href="#"
                        class="text-white hover:text-blue-500 ml-1 border-b border-gray-800">Regístrate</a>
                </p>
            </div>
        </form>
    </div>
@endsection