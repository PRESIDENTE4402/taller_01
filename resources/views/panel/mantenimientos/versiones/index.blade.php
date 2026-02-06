@extends('layouts.panel')

@section('title', 'Gestión de Versiones')
@section('subtitle', 'Catálogo de versiones y equipamiento.')

@section('content')

{{-- Contenedor Principal con Efecto Tech --}}
<div class="max-w-6xl mx-auto relative">
    
    {{-- Decoración de Fondo (Glow sutil Oscuro) --}}
    <div class="absolute -top-10 -right-10 w-64 h-64 bg-slate-900/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-blue-900/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Header de Acciones --}}
    <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">
        
        {{-- Búsqueda Futurista --}}
        <div class="relative w-full sm:w-80 group">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-300 to-cyan-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500"></div>
            <div class="relative flex items-center bg-white rounded-lg">
                <i class="fas fa-search absolute left-4 text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                <input type="text" id="searchInput" placeholder="Buscar versión, modelo..." 
                    class="w-full py-3 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 font-medium rounded-lg"
                    style="outline: none;">
            </div>
        </div>

        {{-- Botón Nueva Versión --}}
        <button onclick="openModal()" 
            class="relative inline-flex items-center justify-center px-8 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-gray-900 rounded-lg group hover:scale-105 shadow-lg hover:shadow-cyan-500/50">
            <span class="absolute top-0 right-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-cyan-500 rounded group-hover:-mr-4 group-hover:-mt-4">
                <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
            </span>
            <span class="absolute bottom-0 rotate-180 left-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-blue-600 rounded group-hover:-ml-4 group-hover:-mb-4">
                <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
            </span>
            <span class="absolute bottom-0 left-0 w-full h-full transition-all duration-500 ease-in-out delay-200 -translate-x-full bg-gradient-to-r from-blue-600 to-cyan-500 rounded-lg group-hover:translate-x-0"></span>
            <span class="relative w-full text-left flex items-center gap-2">
                <i class="fas fa-plus"></i> Nueva Versión
            </span>
        </button>
    </div>

    {{-- Contenedor de Grid de Tarjetas --}}
    <div id="versionesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
        {{-- Las tarjetas serán inyectadas aquí por JS --}}
    </div>

    {{-- Empty State --}}
    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
        <div class="bg-slate-50 p-6 rounded-full mb-4 shadow-inner">
            <i class="fas fa-tags text-slate-300 text-4xl"></i>
        </div>
        <h3 class="text-slate-800 font-bold text-lg">Base de datos vacía</h3>
        <p class="text-slate-500 text-sm mt-2 max-w-xs mx-auto">Registra las versiones disponibles para los modelos de vehículos.</p>
    </div>
</div>

{{-- Modal Tech --}}
<div id="versionModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop con Blur --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            {{-- Panel del Modal --}}
            <div class="relative transform overflow-visible rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-white/20" id="modalPanel">
                
                {{-- Barra Superior Decorativa --}}
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-t-2xl"></div>

                <div class="px-8 pt-8 pb-6">
                    <div class="text-center sm:text-left">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 flex items-center gap-2" id="modalTitle">
                            <i class="fas fa-tag text-cyan-500"></i>
                            <span>Registro de Versión</span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Especifica el modelo y nombre de la versión.</p>
                        
                        <div class="mt-6">
                            <form id="versionForm" onsubmit="saveVersion(event)">
                                <input type="hidden" id="versionId">
                                
                                {{-- Select Modelo --}}
                                <div class="mb-6 relative">
                                    <label for="modeloSelect" class="block text-sm font-medium text-gray-700 mb-2">Modelo de Vehículo</label>
                                    <div class="relative">
                                        <select id="modeloSelect" name="modelo_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-3 px-4 bg-gray-50" required>
                                            <option value="">Seleccione un modelo...</option>
                                            {{-- Options injected by JS --}}
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Input Nombre --}}
                                <div class="group relative z-0 w-full mb-6 max-h-45">
                                    <input type="text" id="nombreVersion" name="nombre" 
                                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" 
                                        placeholder=" " required />
                                    <label for="nombreVersion" 
                                        class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                        Versión (Ej. Limited, Sport, GLX)
                                    </label>
                                    <span class="text-xs text-red-500 mt-1 hidden font-medium" id="errorNombre"></span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50/80 px-8 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100 rounded-b-2xl">
                    <button type="button" onclick="document.getElementById('versionForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))" 
                        class="inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:shadow-cyan-500/30 hover:bg-black transition-all sm:w-auto">
                        Guardar Registro
                    </button>
                    <button type="button" onclick="closeModal()" 
                        class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    const API_URL = "{{ route('panel.mantenimientos.versiones.index') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
</script>
@endsection
@push('scripts')
    @vite(['resources/js/panel/versiones.js'])
@endpush
