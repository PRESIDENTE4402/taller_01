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
                        class="w-full text-left px-4 py-2 rounded-lg bg-purple-50 text-purple-700 text-sm font-bold flex justify-between items-center ring-1 ring-purple-200">
                        <span>Ver Todas</span>
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
                <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                    Órdenes en Curso
                </h3>
                <div class="grid grid-cols-1 gap-4" id="ordersContainer">
                    <!-- Items injected via JS -->
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.routes = {
            list: "{{ route('panel.operaciones.ordenes_trabajo.list') }}",
            create: "{{ route('panel.operaciones.ordenes_trabajo.create') }}",
            print: "{{ route('panel.operaciones.ordenes_trabajo.print', 'ID_PLACEHOLDER') }}"
        };
    </script>
    @vite('resources/js/operaciones/ordenes/index.js')
@endpush