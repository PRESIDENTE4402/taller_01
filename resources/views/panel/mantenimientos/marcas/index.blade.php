@extends('layouts.panel')

@section('title', 'Gestión de Marcas')
@section('subtitle', 'Catálogo maestro de fabricantes.')

@section('content')

{{-- Contenedor Principal con Efecto Tech --}}
<div class="max-w-5xl mx-auto relative">
    
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
                <input type="text" id="searchInput" placeholder="Buscar fabricante..." 
                    class="w-full py-3 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 font-medium rounded-lg"
                    style="outline: none;">
            </div>
        </div>

        {{-- Botón Nueva Marca (Efecto Neon/Tech) --}}
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
                <i class="fas fa-plus"></i> Nueva Marca
            </span>
        </button>
    </div>

    {{-- Contenedor de Grid de Tarjetas --}}
    <div id="marcasGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
        {{-- Las tarjetas serán inyectadas aquí por JS --}}
    </div>

    {{-- Empty State --}}
    <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
        <div class="p-6 rounded-full mb-4">
            <i class="fas fa-layer-group text-slate-300 text-4xl"></i>
        </div>
        <h3 class="text-slate-800 font-bold text-lg">Base de datos vacía</h3>
        <p class="text-slate-500 text-sm mt-2 max-w-xs mx-auto">Inicia registrando las marcas principales para habilitar la gestión de modelos.</p>
    </div>
</div>

{{-- Modal Tech --}}
<div id="marcaModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop con Blur INTENSO --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            {{-- Panel del Modal --}}
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-white/20" id="modalPanel">
                
                {{-- Barra Superior Decorativa --}}
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-cyan-400"></div>

                <div class="px-8 pt-8 pb-6">
                    <div class="text-center sm:text-left">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 flex items-center gap-2" id="modalTitle">
                            <i class="fas fa-cube text-cyan-500"></i>
                            <span>Registro de Marca</span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Ingresa los datos del fabricante para el catálogo.</p>
                        
                        <div class="mt-6">
                            <form id="marcaForm" onsubmit="saveMarca(event)">
                                <input type="hidden" id="marcaId">
                                <div class="group relative z-0 w-full mb-6 max-h-45">
                                    <input type="text" id="nombreMarca" name="nombre" 
                                        class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" 
                                        placeholder=" " required />
                                    <label for="nombreMarca" 
                                        class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                        Nombre de la Marca (Ej. BMW)
                                    </label>
                                    <span class="text-xs text-red-500 mt-1 hidden font-medium" id="errorNombre"></span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50/80 px-8 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100 rounded-b-2xl">
                    <button type="button" class="btn_save_marca inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:shadow-cyan-500/30 hover:bg-black transition-all sm:w-auto" onclick="document.getElementById('marcaForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))">
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

{{-- Modal de Gestión de Modelos --}}
<div id="modelosModal" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity opacity-0" id="modelosBackdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            
            <div class="relative transform overflow-hidden rounded-2xl bg-slate-900 text-left shadow-2xl transition-all sm:w-full sm:max-w-2xl opacity-0 scale-95 border border-white/10" id="modelosPanel">
                
                {{-- Header --}}
                <div class="bg-slate-800/50 px-6 py-4 border-b border-white/5 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-layer-group text-cyan-400"></i>
                            Gestión de Modelos
                        </h3>
                        <p class="text-sm text-slate-400 mt-1">Marca: <span id="marcaTitleName" class="text-cyan-300 font-bold"></span></p>
                    </div>
                    <button onclick="closeModelosModal()" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    {{-- Formulario Agregar Modelo --}}
                    <form id="modeloForm" onsubmit="saveModelo(event)" class="flex gap-3 mb-6">
                        <input type="hidden" id="currentMarcaId">
                        <div class="relative flex-1">
                            <input type="text" id="nombreModelo" name="nombre" 
                                class="w-full bg-slate-800/50 border border-slate-700 rounded-lg py-3 px-4 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all"
                                placeholder="Nuevo Modelo (Ej. Corolla, Civic...)" required>
                        </div>
                        <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-bold rounded-lg shadow-lg shadow-cyan-500/20 transition-all flex items-center gap-2">
                            <i class="fas fa-plus"></i> <span class="hidden sm:inline">Agregar</span>
                        </button>
                    </form>

                    {{-- Lista de Modelos (Scrollable) --}}
                    <div class="bg-slate-950/50 rounded-xl border border-white/5 overflow-hidden max-h-[400px] overflow-y-auto custom-scrollbar">
                        <div id="listaModelos" class="divide-y divide-white/5">
                            {{-- Items inyectados por JS --}}
                        </div>
                        
                        {{-- Empty State Modelos --}}
                        <div id="modelosEmpty" class="hidden py-12 text-center">
                            <i class="fas fa-car-side text-slate-700 text-4xl mb-3"></i>
                            <p class="text-slate-500 text-sm">No hay modelos registrados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scripts --}}
<script>
    const API_URL = "{{ route('panel.mantenimientos.marcas.index') }}";
    const API_MODELOS_URL = "{{ url('panel/mantenimientos/modelos') }}"; // Base URL para modelos
    const CSRF_TOKEN = "{{ csrf_token() }}";
</script>
@endsection


@push('scripts')
    @vite(['resources/js/panel/marcas.js'])
@endpush
