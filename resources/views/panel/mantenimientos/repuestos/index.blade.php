@extends('layouts.panel')

@section('title', 'Gestión de Repuestos (SaaS)')
@section('subtitle', 'Inventario flexible para cualquier tipo de producto.')

@section('content')

    <div class="w-full max-w-[1920px] mx-auto px-4 lg:px-8 xl:px-12 2xl:px-16 relative flex flex-col lg:flex-row gap-8">

        {{-- Background Glow --}}
        <div class="absolute -top-10 -right-10 w-96 h-96 bg-cyan-900/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Sidebar Categorías --}}
        <aside class="w-full lg:w-72 flex-shrink-0 z-10">
            <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white p-6 sticky top-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30">
                        <i class="fas fa-layer-group text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 tracking-tight leading-none mb-1">Categorías</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Filtrar catálogo</p>
                    </div>
                </div>
                
                <div id="categoriasSidebarFilters" class="flex flex-col gap-2">
                    <button onclick="filterByCategory('all')" id="btn-cat-all" class="cat-filter group px-5 py-3.5 rounded-2xl text-sm font-bold bg-blue-600 text-white shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center justify-between w-full">
                        <span class="relative z-10 truncate drop-shadow-sm">Todos los Repuestos</span>
                        <div class="flex items-center gap-2 relative z-10">
                            <span id="count-cat-all" class="text-[10px] font-black bg-white/30 text-white px-2 py-0.5 rounded-full transition-colors" data-count-id="all">0</span>
                            <i class="fas fa-chevron-right text-[10px] opacity-70 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </button>
                    {{-- Dinámico via JS --}}
                </div>
            </div>
        </aside>

        {{-- Area Principal --}}
        <div class="flex-1 min-w-0">
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
            <div id="repuestosGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-6 relative">
            </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-20 text-center">
            <div class="p-6 rounded-full mb-4 bg-slate-100">
                <i class="fas fa-box-open text-slate-300 text-5xl"></i>
            </div>
            <h3 class="text-slate-800 font-bold text-xl">Sin repuestos</h3>
            <p class="text-slate-500 text-sm mt-2">Registra tu inventario de productos.</p>
        </div>
        
        </div> {{-- Fin de Flex-1 Main Content --}}
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
                                    <input type="text" id="codigoRepuesto" readonly
                                        class="w-full rounded-lg border-gray-200 bg-slate-50 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono text-sm cursor-not-allowed text-slate-400"
                                        placeholder="Generado Automáticamente">
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

    {{-- Botón Flotante Carrito --}}
    <div id="cart-float-btn" class="fixed bottom-8 right-8 z-40 hidden">
        <button onclick="toggleCartDrawer()" class="group relative w-16 h-16 bg-blue-600 text-white rounded-full shadow-2xl shadow-blue-600/40 flex items-center justify-center transition-all hover:scale-110 active:scale-95">
            <div class="absolute -inset-2 bg-blue-600 rounded-full blur opacity-20 group-hover:opacity-40 transition-opacity"></div>
            <i class="fas fa-shopping-cart text-xl"></i>
            <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-black w-6 h-6 rounded-full border-2 border-white flex items-center justify-center shadow-lg animate-bounce">0</span>
        </button>
    </div>

    {{-- Drawer del Carrito / Venta Directa --}}
    <div id="cart-drawer" class="fixed inset-y-0 right-0 w-full sm:w-[450px] bg-white shadow-[-20px_0_50px_rgba(0,0,0,0.1)] z-50 translate-x-full transition-transform duration-500 ease-out border-l border-slate-100 flex flex-col">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-lg font-black text-slate-800">Venta Directa</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Resumen de productos</p>
            </div>
            <button onclick="toggleCartDrawer()" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:bg-red-50 hover:text-red-500 hover:border-red-100 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="cart-items-container" class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
            {{-- Items dinámicos --}}
            <div class="flex flex-col items-center justify-center py-20 text-center opacity-40">
                <i class="fas fa-cart-plus text-5xl text-slate-200 mb-4"></i>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">El carrito está vacío</p>
            </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Estimado</span>
                <span id="cart-total" class="text-2xl font-black text-slate-800">$0.00</span>
            </div>
            
            {{-- Sección de Cliente --}}
            <div class="space-y-4 pt-2 border-t border-slate-200/50 mt-2">
                <div class="flex items-center justify-between ml-1">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Información del Cliente</label>
                    <button id="toggle-new-client" onclick="toggleNewClientForm()" class="text-[9px] font-black text-blue-600 uppercase tracking-widest hover:underline">
                        + Nuevo Cliente
                    </button>
                </div>

                {{-- Buscador de Cliente --}}
                <div id="client-search-container" class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" id="cart-client-search" onkeyup="searchClients(this.value)" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all" placeholder="Buscar por nombre, NIT o teléfono...">
                    
                    {{-- Resultados de búsqueda --}}
                    <div id="client-results" class="absolute bottom-full left-0 w-full bg-white rounded-2xl shadow-2xl border border-slate-100 mb-2 max-h-48 overflow-y-auto hidden z-50 divide-y divide-slate-50">
                        {{-- Dinámico --}}
                    </div>
                </div>

                {{-- Cliente Seleccionado (Badge) --}}
                <div id="selected-client-badge" class="hidden flex items-center justify-between bg-blue-50 border border-blue-100 rounded-2xl p-4 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black">
                            <span id="client-initial">C</span>
                        </div>
                        <div class="flex flex-col">
                            <span id="selected-client-name" class="text-sm font-black text-blue-800">Nombre del Cliente</span>
                            <span id="selected-client-phone" class="text-[10px] font-bold text-blue-400 uppercase tracking-widest">0000-0000</span>
                        </div>
                    </div>
                    <button onclick="deselectClient()" class="w-8 h-8 rounded-lg bg-white text-slate-400 hover:text-red-500 hover:border-red-100 border border-slate-200 transition-all flex items-center justify-center">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Formulario Nuevo Cliente (Oculto por defecto) --}}
                <div id="new-client-form" class="hidden space-y-3 animate-fade-in-up">
                    <input type="text" id="new-client-name" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700" placeholder="Nombre completo / Empresa">
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" id="new-client-phone" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700" placeholder="Teléfono">
                        <input type="text" id="new-client-nit" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700" placeholder="NIT (C/F)">
                    </div>
                </div>

                <input type="hidden" id="selected-client-id" value="">
            </div>

            <div class="space-y-3">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nota de Venta (Opcional)</label>
                <textarea id="cart-notes" class="w-full rounded-2xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 text-sm p-4 h-20 resize-none" placeholder="Ej. Pago en efectivo, entrega programada..."></textarea>
            </div>

            <button onclick="processCheckout()" id="checkout-btn" class="w-full bg-slate-900 text-white rounded-2xl py-4 font-black flex items-center justify-center gap-3 shadow-xl hover:bg-black transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                <span>FINALIZAR VENTA</span>
                <i class="fas fa-check-circle"></i>
            </button>
        </div>
    </div>

    {{-- Backdrop para el Drawer --}}
    <div id="cart-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity" onclick="toggleCartDrawer()"></div>
@endsection

@push('scripts')
    @vite(['resources/js/panel/repuestos.js'])
@endpush