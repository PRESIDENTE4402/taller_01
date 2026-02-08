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
                 <button class="w-full text-left px-4 py-2 rounded-lg bg-purple-50 text-purple-700 text-sm font-bold flex justify-between items-center ring-1 ring-purple-200">
                     <span>Ver Todas</span>
                 </button>
             </div>
        </div>

        <a href="{{ route('panel.operaciones.ordenes_trabajo.create') }}" class="btn btn-primary w-full gap-2 shadow-lg shadow-purple-500/30">
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
    document.addEventListener('DOMContentLoaded', () => {
        loadData();
    });

    async function loadData() {
        try {
            const containerPending = document.getElementById('pendingContainer');
            const containerOrders = document.getElementById('ordersContainer');
            const sectionPending = document.getElementById('pendingSection');
            
            // Loading State
            containerOrders.innerHTML = '<div class="text-center py-10 text-gray-400"><i class="fas fa-circle-notch fa-spin text-2xl"></i></div>';

            const response = await fetch("{{ route('panel.operaciones.ordenes_trabajo.list') }}");
            const data = await response.json();

            // 1. Render Pendientes (Citas)
            if (data.citas_pendientes.length > 0) {
                sectionPending.classList.remove('hidden');
                containerPending.innerHTML = data.citas_pendientes.map(cita => createPendingCard(cita)).join('');
            } else {
                sectionPending.classList.add('hidden');
                containerPending.innerHTML = '';
            }

            // 2. Render Órdenes Activas
            if (data.ordenes.length > 0) {
                containerOrders.innerHTML = data.ordenes.map(orden => createOrderCard(orden)).join('');
                document.getElementById('countActive').innerText = data.ordenes.length;
            } else {
                containerOrders.innerHTML = `
                    <div class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                        <p class="text-gray-400 font-medium">No hay órdenes activas</p>
                    </div>
                `;
                document.getElementById('countActive').innerText = 0;
            }

        } catch (error) {
            console.error(error);
        }
    }

    function createPendingCard(cita) {
        const createUrl = "{{ route('panel.operaciones.ordenes_trabajo.create') }}?cita_id=" + cita.id;
        
        return `
            <div class="bg-white p-4 rounded-xl border-l-[6px] border-green-500 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xl">
                        <i class="fas fa-car-side"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-lg">${cita.cliente?.nombre_completo || 'Cliente'}</h4>
                        <p class="text-sm text-gray-600">
                            ${cita.vehiculo ? `${cita.vehiculo.marca?.nombre} ${cita.vehiculo.modelo?.nombre} (${cita.vehiculo.placa})` : 'Vehículo no identificado'}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            <i class="far fa-clock"></i> Llegó: ${new Date(cita.updated_at).toLocaleString()}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="${createUrl}" class="btn bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg shadow-green-500/30 flex items-center gap-2 w-full md:w-auto justify-center">
                        <span>Empezar Orden</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        `;
    }

    function createOrderCard(orden) {
        // Status Colors
        const statusColors = {
            'abierta': 'bg-blue-100 text-blue-700 border-blue-200',
            'en_proceso': 'bg-purple-100 text-purple-700 border-purple-200',
            'espera_repuesto': 'bg-orange-100 text-orange-700 border-orange-200',
            'finalizada': 'bg-green-100 text-green-700 border-green-200'
        };
        const statusLabels = {
            'abierta': 'Abierta',
            'en_proceso': 'En Proceso',
            'espera_repuesto': 'Espera Repuesto',
            'finalizada': 'Finalizada'
        };

        const badge = statusColors[orden.estado] || 'bg-gray-100 text-gray-600';
        const label = statusLabels[orden.estado] || orden.estado;

        return `
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow relative">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Orden #${orden.codigo_orden}</span>
                        <h4 class="font-bold text-gray-800 text-lg">${orden.cliente?.nombre_completo || 'Cliente'}</h4>
                    </div>
                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider border ${badge}">
                        ${label}
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-car text-gray-400"></i>
                         ${orden.vehiculo ? `${orden.vehiculo.marca?.nombre} ${orden.vehiculo.modelo?.nombre}` : 'Vehículo'}
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-tachometer-alt text-gray-400"></i>
                         ${orden.kilometraje_entrada} km
                    </div>
                </div>

                <div class="border-t border-gray-50 pt-3 flex justify-end gap-2">
                    <button class="text-gray-400 hover:text-blue-600 p-2 transition-colors" title="Ver Detalles">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-gray-400 hover:text-green-600 p-2 transition-colors" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-gray-400 hover:text-purple-600 p-2 transition-colors" title="Imprimir">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        `;
    }
</script>
@endpush
