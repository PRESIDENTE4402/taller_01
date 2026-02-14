@extends('layouts.panel')

@section('title', 'Gestión de Repuestos (SaaS)')
@section('subtitle', 'Inventario flexible para cualquier tipo de producto.')

@section('content')

    <div class="max-w-7xl mx-auto relative">

        {{-- Background Glow --}}
        <div class="absolute -top-10 -right-10 w-96 h-96 bg-cyan-900/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">

            <div class="relative w-full sm:w-80 group">
                <div
                    class="absolute -inset-0.5 bg-gradient-to-r from-cyan-300 to-blue-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500">
                </div>
                <div class="relative flex items-center bg-white rounded-lg">
                    <i class="fas fa-search absolute left-4 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar repuesto..."
                        class="w-full py-3 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 font-medium rounded-lg"
                        style="outline: none;">
                </div>
            </div>

            <button onclick="openModal()"
                class="relative inline-flex items-center justify-center px-8 py-3 overflow-hidden font-bold text-white transition-all duration-300 bg-slate-900 rounded-lg group hover:scale-105 shadow-lg">
                <span
                    class="absolute top-0 right-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-cyan-500 rounded group-hover:-mr-4 group-hover:-mt-4">
                    <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
                </span>
                <span
                    class="absolute bottom-0 rotate-180 left-0 inline-block w-4 h-4 transition-all duration-500 ease-in-out bg-blue-600 rounded group-hover:-ml-4 group-hover:-mb-4">
                    <span class="absolute top-0 right-0 w-5 h-5 rotate-45 translate-x-1/2 -translate-y-1/2 bg-white"></span>
                </span>
                <span
                    class="absolute bottom-0 left-0 w-full h-full transition-all duration-500 ease-in-out delay-200 -translate-x-full bg-gradient-to-r from-blue-600 to-cyan-500 rounded-lg group-hover:translate-x-0"></span>
                <span class="relative w-full text-left flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nuevo Repuesto
                </span>
            </button>
        </div>

        {{-- Grid --}}
        <div id="repuestosGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 relative">
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-20 text-center">
            <div class="p-6 rounded-full mb-4 bg-slate-100">
                <i class="fas fa-box-open text-slate-300 text-5xl"></i>
            </div>
            <h3 class="text-slate-800 font-bold text-xl">Sin repuestos</h3>
            <p class="text-slate-500 text-sm mt-2">Registra tu inventario de productos.</p>
        </div>
    </div>

    {{-- Modal --}}
    <div id="repuestoModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                {{-- Increased max-width for more fields --}}
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    id="modalPanel">

                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-cyan-500 to-blue-600"></div>

                    <div class="px-8 pt-8 pb-6">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2" id="modalTitle">
                            <i class="fas fa-box text-blue-500"></i>
                            <span>Nuevo Repuesto</span>
                        </h3>

                        <form id="repuestoForm" onsubmit="saveRepuesto(event)"
                            class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" id="repuestoId">

                            {{-- Columna 1: Info Básica --}}
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Información
                                    General</h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Producto</label>
                                    <input type="text" id="nombreRepuesto" required
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                        placeholder="Ej. Filtro de Aceite">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Código Interno (SKU)</label>
                                    <input type="text" id="codigoRepuesto" required
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono text-sm"
                                        placeholder="SKU-12345">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                        <input type="text" id="marcaRepuesto"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                            placeholder="Ej. Bosch">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                                        <select id="categoriaRepuesto"
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                                            {{-- Injected by JS --}}
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio Costo</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" step="0.01" id="precioCosto" required
                                                class="w-full rounded-lg border-gray-300 pl-7 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio Venta</label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">$</span>
                                            </div>
                                            <input type="number" step="0.01" id="precioVenta" required
                                                class="w-full rounded-lg border-gray-300 pl-7 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Actual</label>
                                        <input type="number" id="stockActual" required
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                            placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo</label>
                                        <input type="number" id="stockMinimo" required
                                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                            placeholder="0">
                                    </div>
                                </div>
                            </div>

                            {{-- Columna 2: Atributos Dinámicos --}}
                            <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Características
                                        (SaaS)</h4>
                                    <button type="button" onclick="addAttributeRow()"
                                        class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded hover:bg-blue-200 transition-colors font-bold">
                                        <i class="fas fa-plus mr-1"></i> Agregar Info
                                    </button>
                                </div>

                                <div class="text-xs text-gray-400 mb-3 italic">
                                    Agrega campos personalizados para este producto (ej. Voltaje, Talla, Material).
                                </div>

                                <div id="attributesContainer"
                                    class="space-y-3 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                                    {{-- Dynamic Rows Here --}}
                                </div>
                            </div>

                        </form>
                    </div>

                    <div class="bg-gray-50 px-8 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100">
                        <button type="button"
                            onclick="document.getElementById('repuestoForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))"
                            class="inline-flex w-full justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-black transition-all sm:w-auto shadow-lg hover:shadow-cyan-500/30">
                            Guardar Producto
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
        const API_URL = "{{ route('panel.mantenimientos.repuestos.index') }}";
        const API_CATEGORIAS_URL = "{{ route('panel.mantenimientos.categorias.index') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/panel/repuestos.js'])
@endpush