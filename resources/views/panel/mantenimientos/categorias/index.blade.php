@extends('layouts.panel')

@section('title', 'Gestión de Categorías')
@section('subtitle', 'Organiza tu inventario por tipos de productos.')

@section('content')

    {{-- Contenedor Principal --}}
    <div class="max-w-5xl mx-auto relative">

        {{-- Decoración de Fondo --}}
        <div class="absolute -top-10 -right-10 w-64 h-64 bg-slate-900/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Header de Acciones --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">

            {{-- Búsqueda --}}
            <div class="relative w-full sm:w-80 group">
                <div
                    class="absolute -inset-0.5 bg-gradient-to-r from-purple-300 to-blue-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500">
                </div>
                <div class="relative flex items-center bg-white rounded-lg">
                    <i class="fas fa-search absolute left-4 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar categoría..."
                        class="w-full py-3 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 font-medium rounded-lg"
                        style="outline: none;">
                </div>
            </div>

            {{-- Botón Nueva Categoría --}}
            <button onclick="openModal()"
                class="relative inline-flex items-center justify-center px-8 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-slate-900 rounded-lg group hover:scale-105 shadow-lg">
                <span
                    class="absolute top-0 right-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-purple-500 rounded group-hover:-mr-4 group-hover:-mt-4">
                    <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
                </span>
                <span
                    class="absolute bottom-0 rotate-180 left-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-blue-600 rounded group-hover:-ml-4 group-hover:-mb-4">
                    <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
                </span>
                <span
                    class="absolute bottom-0 left-0 w-full h-full transition-all duration-500 ease-in-out delay-200 -translate-x-full bg-gradient-to-r from-blue-600 to-purple-500 rounded-lg group-hover:translate-x-0"></span>
                <span class="relative w-full text-left flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </span>
            </button>
        </div>

        {{-- Grid de Categorías --}}
        <div id="categoriasGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative">
            {{-- Cards inyectadas por JS --}}
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center">
            <div class="p-6 rounded-full mb-4 bg-slate-100">
                <i class="fas fa-tags text-slate-300 text-4xl"></i>
            </div>
            <h3 class="text-slate-800 font-bold text-lg">No hay categorías</h3>
            <p class="text-slate-500 text-sm mt-2">Crea categorías para organizar tus repuestos.</p>
        </div>
    </div>

    {{-- Modal --}}
    <div id="categoriaModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    id="modalPanel">

                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-purple-500"></div>

                    <div class="px-8 pt-8 pb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2" id="modalTitle">
                            <i class="fas fa-tag text-purple-500"></i>
                            <span>Nueva Categoría</span>
                        </h3>

                        <form id="categoriaForm" onsubmit="saveCategoria(event)" class="mt-6 space-y-4">
                            <input type="hidden" id="categoriaId">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input type="text" id="nombreCategoria" required
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm"
                                    placeholder="Ej. Electrónica, Frenos...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                <textarea id="descripcionCategoria" rows="2"
                                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm"
                                    placeholder="Breve descripción..."></textarea>
                            </div>
                        </form>
                    </div>

                    <div class="bg-gray-50 px-8 py-4 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button"
                            onclick="document.getElementById('categoriaForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))"
                            class="inline-flex w-full justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-black transition-all sm:w-auto shadow-lg hover:shadow-purple-500/30">
                            Guardar
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "{{ route('panel.mantenimientos.categorias.index') }}"; // Base URL
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/panel/categorias.js'])
@endpush