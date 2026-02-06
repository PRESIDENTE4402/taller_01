@extends('layouts.panel')

@section('title', 'Gestión de Versiones')
@section('subtitle', 'Catálogo de versiones y equipamiento.')

@section('content')

{{-- Contenedor Principal con Efecto Tech --}}
<div class="max-w-6xl mx-auto relative">
    
    {{-- Decoración de Fondo (Glow sutil Oscuro) --}}
    <div class="absolute -top-10 -right-10 w-64 h-64 bg-slate-900/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-blue-900/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Header de Navegación y Búsqueda --}}
    <div class="flex flex-col gap-6 mb-8 relative z-10">
        
        {{-- Breadcrumbs / Título --}}
        <div class="flex items-center gap-4">
            {{-- Botón Volver --}}
            <button id="btnBack" onclick="goBack()" class="hidden h-10 w-10 text-white bg-slate-800 hover:bg-slate-700 rounded-xl items-center justify-center transition-all shadow-lg hover:shadow-cyan-500/20 border border-slate-700">
                <i class="fas fa-arrow-left"></i>
            </button>
            
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight flex items-center gap-2" id="viewTitle">
                    Marcas
                </h2>
                <p class="text-slate-400 font-medium" id="viewSubtitle">Selecciona una marca para explorar sus modelos</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            {{-- Búsqueda --}}
            <div class="relative w-full sm:w-96 group">
                <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl blur opacity-20 group-hover:opacity-60 transition duration-500"></div>
                <div class="relative flex items-center bg-slate-900 rounded-xl border border-slate-700">
                    <i class="fas fa-search absolute left-4 text-slate-500 group-focus-within:text-cyan-400 transition-colors"></i>
                    <input type="text" id="searchInput" placeholder="Buscar..." 
                        class="w-full py-3.5 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-white placeholder-slate-500 font-medium rounded-xl"
                        style="outline: none;">
                </div>
            </div>

            {{-- Botón Nueva Versión (Solo visible en nivel de modelos, manejado por JS) --}}
            <!-- Este botón global se elimina, ahora se agrega contexto específico en cada tarjeta de modelo -->
        </div>
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

{{-- Modal Nuevo: Gestión de Versiones de un Modelo --}}
<div id="versionesListModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity opacity-0" id="versionesListBackdrop"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <input type="hidden" id="currentModeloIdList">
            <input type="hidden" id="currentMarcaIdList">

            <div class="relative transform overflow-hidden rounded-2xl bg-[#0f172a] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-700 opacity-0 scale-95" id="versionesListPanel">
                
                {{-- Header Modal --}}
                <div class="bg-slate-800 px-4 py-4 sm:px-6 flex justify-between items-center border-b border-slate-700">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-2" id="versionesListTitle">
                            {{-- JS inyectará el título --}}
                        </h3>
                        <p class="text-sm text-slate-400 mt-0.5">Gestión de versiones disponibles</p>
                    </div>
                    <button type="button" onclick="closeVersionesListModal()" class="text-slate-400 hover:text-white transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="px-4 py-6 sm:px-6 bg-[#0f172a]">
                    
                    {{-- Formulario Rápido Agregar --}}
                    <div class="mb-6 bg-slate-800/50 p-4 rounded-xl border border-slate-700/50">
                        <form id="quickVersionForm" onsubmit="saveQuickVersion(event)" class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">Nueva Versión</label>
                                <input type="text" id="quickNombreVersion" 
                                    class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white placeholder-slate-500 focus:ring-1 focus:ring-cyan-500 focus:border-cyan-500 text-sm font-medium" 
                                    placeholder="Ej. LE, XLE, Sport..." required>
                            </div>
                            <button type="submit" class="bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-2 px-4 rounded-lg transition-colors flex items-center gap-2 text-sm h-[38px]">
                                <i class="fas fa-plus"></i> Agregar
                            </button>
                        </form>
                    </div>

                    {{-- Lista de Versiones --}}
                    <div class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar" id="listaVersionesContainer">
                        {{-- Inyectado por JS --}}
                    </div>

                    {{-- Empty State (Oculto por defecto) --}}
                    <div id="versionesListEmpty" class="hidden flex-col items-center justify-center py-8 text-center text-slate-500">
                        <i class="fas fa-info-circle text-2xl mb-2 opacity-50"></i>
                        <p class="text-sm">No hay versiones registradas.</p>
                    </div>

                </div>

                <div class="bg-slate-800 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-700">
                    <button type="button" onclick="closeVersionesListModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-slate-600 sm:mt-0 sm:w-auto transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Mantener el modal de edición/creación "Completo" (Si se quiere usar) o eliminarlo. 
     Para MVP, usaremos el "quick form" para agregar y quizas SweetAlert para editar al vuelo 
     como en Modelos. Es más rápido y consistente. Comentemos el viejo modal. --}}
<!-- Old Modal Removed to avoid duplication and confusion -->

{{-- Scripts --}}
<script>
    const API_URL = "{{ route('panel.mantenimientos.versiones.index') }}";
    const API_MODELOS_URL = "{{ url('panel/mantenimientos/modelos') }}";
    const API_MARCAS_URL = "{{ route('panel.mantenimientos.marcas.list') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";
</script>
@endsection
@push('scripts')
    @vite(['resources/js/panel/versiones.js'])
@endpush
