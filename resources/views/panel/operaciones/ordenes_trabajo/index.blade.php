@extends('layouts.panel')

@section('title', 'Órdenes de Trabajo')
@section('subtitle', 'Gestiona las reparaciones y servicios en curso')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-full">

        <!-- Sidebar Filtros -->
        <div class="lg:col-span-1 flex flex-col gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-600">
                <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider mb-1">En Taller</h3>
                <div class="flex items-end gap-2">
                    <span class="text-4xl font-black text-gray-800" id="countActive">0</span>
                    <span class="text-sm text-gray-400 mb-2 font-medium">órdenes activas</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <h4 class="font-bold text-gray-700 mb-4">Filtrar por Estado</h4>
                <div class="space-y-2">
                    <button
                        class="filter-btn w-full text-left px-4 py-2 rounded-lg bg-purple-50 text-purple-700 text-sm font-bold flex justify-between items-center ring-1 ring-purple-200"
                        data-status="all">
                        <span>Ver Todas</span>
                    </button>
                    <button
                        class="filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors"
                        data-status="por_planificar">
                        <span>Por Planificar</span>
                    </button>
                    <button
                        class="filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors"
                        data-status="en_proceso">
                        <span>En Proceso</span>
                    </button>
                    <button
                        class="filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors"
                        data-status="espera_repuesto">
                        <span>Espera Repuesto</span>
                    </button>
                    <button
                        class="filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors"
                        data-status="finalizada">
                        <span>Finalizadas</span>
                    </button>
                    <button
                        class="filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors"
                        data-status="entregada">
                        <span>Entregadas</span>
                    </button>
                </div>
            </div>

            <a href="{{ route('panel.operaciones.ordenes_trabajo.create') }}"
                class="btn btn-primary w-full gap-2 shadow-lg shadow-purple-500/30">
                <i class="fas fa-plus"></i> Nueva Orden Manual
            </a>
        </div>

        <!-- Contenido Principal -->
        <div class="lg:col-span-3 space-y-6 overflow-y-auto custom-scrollbar pr-2 h-[calc(100vh-140px)]">

            <!-- SECCION 1: VEHÍCULOS EN ESPERA (Citas Concretadas) -->
            <div id="pendingSection" class="hidden">
                <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-2 h-6 bg-green-500 rounded-full"></span>
                    Recepción Pendiente (Citas Concretadas)
                </h3>
                <div class="grid grid-cols-1 gap-4" id="pendingContainer">
                    <!-- Items injected via JS -->
                </div>
            </div>

            <!-- SECCION 2: ÓRDENES ACTIVAS -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                        Órdenes en Curso
                    </h3>
                    <div class="relative w-full max-w-xs">
                        <input type="text" id="searchInput" placeholder="Buscar por placa o cliente..."
                            class="input input-sm input-bordered w-full pr-10 rounded-lg">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="ordersContainer"
                    data-list-url="{{ route('panel.operaciones.ordenes_trabajo.list') }}"
                    data-create-url="{{ route('panel.operaciones.ordenes_trabajo.create') }}">
                    <!-- Items injected via JS -->
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Visualizar Orden -->
    <input type="checkbox" id="modal-view-order" class="modal-toggle" />
    <div class="modal modal-bottom sm:modal-middle">
        <div class="modal-box w-11/12 max-w-4xl bg-white p-0 overflow-hidden rounded-2xl">
            <div id="modalLoading" class="p-20 text-center text-gray-400">
                <i class="fas fa-circle-notch fa-spin text-4xl mb-4"></i>
                <p class="font-bold uppercase tracking-widest text-xs">Cargando detalles...</p>
            </div>
            <div id="modalContent" class="hidden">
                <!-- Contenido dinámico via JS -->
            </div>
        </div>
        <label class="modal-backdrop" for="modal-view-order">Close</label>
    </div>

@endsection

@push('scripts')
    <script>
        window.routes = {
            list: "{{ route('panel.operaciones.ordenes_trabajo.list') }}",
            create: "{{ route('panel.operaciones.ordenes_trabajo.create') }}",
            show: "{{ route('panel.operaciones.ordenes_trabajo.show', 'ID_PLACEHOLDER') }}",
            details: "{{ route('panel.operaciones.ordenes_trabajo.details', 'ID_PLACEHOLDER') }}",
            print: "{{ route('panel.operaciones.ordenes_trabajo.print', 'ID_PLACEHOLDER') }}"
        };
    </script>
    @vite('resources/js/operaciones/ordenes/index.js')
@endpush