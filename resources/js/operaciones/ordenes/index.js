document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ordersContainer');
    if (container) {
        const listUrl = container.dataset.listUrl;
        const createUrlBase = container.dataset.createUrl;
        loadData(listUrl, createUrlBase);
    }
});

/**
 * Carga los datos de órdenes y citas desde el servidor
 * @param {string} listUrl 
 * @param {string} createUrlBase 
 */
async function loadData(listUrl, createUrlBase) {
    try {
        const containerPending = document.getElementById('pendingContainer');
        const containerOrders = document.getElementById('ordersContainer');
        const sectionPending = document.getElementById('pendingSection');

        if (!containerOrders) return;

        // Loading State
        containerOrders.innerHTML = '<div class="text-center py-10 text-gray-400"><i class="fas fa-circle-notch fa-spin text-2xl"></i></div>';

        const response = await fetch(listUrl);
        const data = await response.json();

        // 1. Render Pendientes (Citas)
        if (data.citas_pendientes && data.citas_pendientes.length > 0) {
            sectionPending.classList.remove('hidden');
            containerPending.innerHTML = data.citas_pendientes.map(cita => createPendingCard(cita, createUrlBase)).join('');
        } else {
            sectionPending.classList.add('hidden');
            containerPending.innerHTML = '';
        }

        // 2. Render Órdenes Activas
        if (data.ordenes && data.ordenes.length > 0) {
            containerOrders.innerHTML = data.ordenes.map(orden => createOrderCard(orden)).join('');
            const countBadge = document.getElementById('countActive');
            if (countBadge) countBadge.innerText = data.ordenes.length;
        } else {
            containerOrders.innerHTML = `
                <div class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300 w-full">
                    <p class="text-gray-400 font-medium">No hay órdenes activas</p>
                </div>
            `;
            const countBadge = document.getElementById('countActive');
            if (countBadge) countBadge.innerText = 0;
        }

    } catch (error) {
        console.error('Error loading data:', error);
    }
}

/**
 * Crea el HTML para una tarjeta de cita pendiente
 * @param {Object} cita 
 * @param {string} createUrlBase 
 * @returns {string}
 */
function createPendingCard(cita, createUrlBase) {
    const createUrl = `${createUrlBase}?cita_id=${cita.id}`;

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

/**
 * Crea el HTML para una tarjeta de orden activa
 * @param {Object} orden 
 * @returns {string}
 */
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

    // Build functional URLs based on the container data (or pass them globally)
    const baseUrl = window.location.origin + '/panel/operaciones/ordenes-trabajo';
    const showUrl = `${baseUrl}/${orden.id}`;
    const editUrl = `${baseUrl}/${orden.id}/edit`;
    const printUrl = `${baseUrl}/${orden.id}/print`;

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
                     ${number_format(orden.kilometraje_entrada)} km
                </div>
            </div>

            <div class="border-t border-gray-50 pt-3 flex justify-end gap-2">
                <a href="${showUrl}" class="btn btn-ghost btn-sm text-gray-400 hover:text-blue-600 p-2 transition-colors" title="Planificar / Ver Detalles">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="${editUrl}" class="btn btn-ghost btn-sm text-gray-400 hover:text-green-600 p-2 transition-colors" title="Editar Recepción">
                    <i class="fas fa-edit"></i>
                </a>
                <a href="${printUrl}" target="_blank" class="btn btn-ghost btn-sm text-gray-400 hover:text-purple-600 p-2 transition-colors" title="Imprimir Recepción">
                    <i class="fas fa-print"></i>
                </a>
            </div>
        </div>
    `;
}

// Helper function
function number_format(number) {
    return new Intl.NumberFormat().format(number);
}
