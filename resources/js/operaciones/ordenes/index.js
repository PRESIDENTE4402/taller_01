document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ordersContainer');
    if (container) {
        const listUrl = container.dataset.listUrl;
        const createUrlBase = container.dataset.createUrl;

        let allOrders = [];
        let currentFilter = 'all';
        let currentSearch = '';

        // Check if there is an initial filter in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const initialFilter = urlParams.get('filter');
        if (initialFilter) {
            currentFilter = initialFilter;
            // Update UI for the initial filter button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.status === currentFilter) {
                    btn.className = "filter-btn w-full text-left px-4 py-2 rounded-lg bg-purple-50 text-purple-700 text-sm font-bold flex justify-between items-center ring-1 ring-purple-200";
                } else {
                    btn.className = "filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors";
                }
            });
        }

        // Initial Load
        loadData();

        // --- Filtering Logic ---
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                // Update UI state
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.className = "filter-btn w-full text-left px-4 py-2 rounded-lg hover:bg-gray-50 text-gray-600 text-sm font-medium flex justify-between items-center transition-colors";
                });
                this.className = "filter-btn w-full text-left px-4 py-2 rounded-lg bg-purple-50 text-purple-700 text-sm font-bold flex justify-between items-center ring-1 ring-purple-200";

                currentFilter = this.dataset.status;
                applyFilters();
            });
        });

        // --- Search Logic ---
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                currentSearch = this.value.toLowerCase();
                applyFilters();
            });
        }

        async function loadData() {
            try {
                const containerPending = document.getElementById('pendingContainer');
                const sectionPending = document.getElementById('pendingSection');

                if (!container) return;

                // Loading State
                container.innerHTML = '<div class="col-span-full text-center py-10 text-gray-400"><i class="fas fa-circle-notch fa-spin text-2xl mb-2"></i><p class="text-xs uppercase tracking-widest">Cargando órdenes...</p></div>';

                const response = await fetch(listUrl);
                const data = await response.json();

                allOrders = data.ordenes || [];

                // 1. Render Pendientes (Citas) - No filtradas por ahora
                if (data.citas_pendientes && data.citas_pendientes.length > 0) {
                    sectionPending.classList.remove('hidden');
                    containerPending.innerHTML = data.citas_pendientes.map(cita => createPendingCard(cita, createUrlBase)).join('');
                } else {
                    sectionPending.classList.add('hidden');
                    containerPending.innerHTML = '';
                }

                // 2. Initial Render of Orders
                applyFilters();

            } catch (error) {
                console.error('Error loading data:', error);
                container.innerHTML = '<div class="col-span-full text-center py-10 text-red-400"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error al cargar los datos</p></div>';
            }
        }

        function applyFilters() {
            let filtered = allOrders;

            // Apply Status Filter
            if (currentFilter !== 'all') {
                if (currentFilter === 'por_planificar') {
                    // Una orden está "por planificar" si está abierta y NO tiene tareas asignadas
                    filtered = filtered.filter(o => o.estado === 'abierta' && (!o.bitacoras || o.bitacoras.length === 0));
                } else {
                    filtered = filtered.filter(o => o.estado === currentFilter);
                }
            }

            // Apply Search
            if (currentSearch) {
                filtered = filtered.filter(o =>
                    o.codigo_orden.toLowerCase().includes(currentSearch) ||
                    o.cliente?.nombre_completo.toLowerCase().includes(currentSearch) ||
                    o.vehiculo?.placa.toLowerCase().includes(currentSearch)
                );
            }

            renderOrders(filtered);
        }

        function renderOrders(orders) {
            const countBadge = document.getElementById('countActive');
            if (countBadge) countBadge.innerText = orders.length;

            if (orders.length > 0) {
                container.innerHTML = orders.map(orden => createOrderCard(orden)).join('');
                // Attach details listeners
                attachDetailEvents();
            } else {
                container.innerHTML = `
                    <div class="col-span-full text-center py-20 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                            <i class="fas fa-folder-open text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-bold">No se encontraron órdenes</p>
                        <p class="text-gray-400 text-xs mt-1">Intenta con otros filtros o búsqueda</p>
                    </div>
                `;
            }
        }
    }
});

/**
 * Adjunta eventos "click" a los botones de ver detalles
 */
function attachDetailEvents() {
    document.querySelectorAll('.btn-view-details').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            openOrderModal(id);
        });
    });
}

/**
 * Abre el modal y carga los detalles de la orden
 */
async function openOrderModal(id) {
    const checkbox = document.getElementById('modal-view-order');
    const loading = document.getElementById('modalLoading');
    const content = document.getElementById('modalContent');

    checkbox.checked = true;
    loading.classList.remove('hidden');
    content.classList.add('hidden');

    try {
        const url = window.routes.details.replace('ID_PLACEHOLDER', id);
        const response = await fetch(url);
        const orden = await response.json();

        renderModalContent(orden);

        loading.classList.add('hidden');
        content.classList.remove('hidden');
    } catch (error) {
        console.error('Error fetching details:', error);
        content.innerHTML = '<div class="p-20 text-center text-red-500"><i class="fas fa-times-circle text-4xl mb-2"></i><p>Error al cargar detalles</p></div>';
        loading.classList.add('hidden');
        content.classList.remove('hidden');
    }
}

/**
 * Renderiza el contenido del modal con los datos de la orden
 */
function renderModalContent(orden) {
    const container = document.getElementById('modalContent');
    const statusColors = {
        'abierta': 'bg-blue-100 text-blue-700',
        'en_proceso': 'bg-purple-100 text-purple-700',
        'espera_repuesto': 'bg-orange-100 text-orange-700',
        'detenida': 'bg-orange-100 text-orange-700',
        'finalizada': 'bg-green-100 text-green-700',
        'entregada': 'bg-gray-100 text-gray-700'
    };

    const editUrl = window.routes.create.replace('/create', `/${orden.id}/edit`);
    const printUrl = window.routes.print.replace('ID_PLACEHOLDER', orden.id);

    container.innerHTML = `
        <!-- Header -->
        <div class="bg-gray-900 p-6 text-white flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="text-xs font-black uppercase tracking-widest text-blue-400">Orden de Trabajo</span>
                    <span class="px-3 py-0.5 rounded-full text-[10px] font-bold uppercase ${statusColors[orden.estado] || 'bg-gray-100 text-gray-800'}">
                        ${orden.estado.replace('_', ' ')}
                    </span>
                </div>
                <h2 class="text-2xl font-black italic tracking-tighter">#${orden.codigo_orden}</h2>
                ${orden.motivo_estado ? `<p class="text-[10px] bg-orange-500/20 text-orange-300 px-2 py-0.5 rounded mt-1 font-bold italic border border-orange-500/30 inline-block truncate max-w-full"><i class="fas fa-info-circle mr-1"></i> ${orden.motivo_estado}</p>` : ''}
            </div>
            <div class="flex gap-2">
                <a href="${printUrl}" target="_blank" class="btn btn-sm bg-white/10 hover:bg-white/20 border-none text-white gap-2">
                    <i class="fas fa-print"></i> PDF
                </a>
                <a href="${editUrl}" class="btn btn-sm bg-blue-600 hover:bg-blue-700 border-none text-white gap-2">
                    <i class="fas fa-edit"></i> EDITAR
                </a>
                <label for="modal-view-order" class="btn btn-sm btn-ghost text-white h-8 w-8 min-h-0 p-0">
                    <i class="fas fa-times"></i>
                </label>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6 overflow-y-auto max-h-[70vh] custom-scrollbar">
            <!-- Columna 1: Info Cliente y Vehículo -->
            <div class="md:col-span-1 space-y-6">
                <section>
                    <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-user-circle"></i> Cliente
                    </h4>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <p class="font-black text-gray-800 leading-none mb-1">${orden.cliente?.nombre_completo}</p>
                        <p class="text-xs text-gray-500 font-bold uppercase tracking-tight">${orden.cliente?.telefono || 'Sin Teléfono'}</p>
                    </div>
                </section>

                <section>
                    <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-2">
                        <i class="fas fa-car"></i> Vehículo
                    </h4>
                    <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                        <div class="text-xl font-black text-blue-900 leading-none mb-1">${orden.vehiculo?.placa}</div>
                        <p class="text-xs font-bold text-blue-700 uppercase">${orden.vehiculo?.marca?.nombre} ${orden.vehiculo?.modelo?.nombre}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-1 bg-white rounded text-[10px] font-bold text-gray-500 border border-blue-100">${orden.vehiculo?.anio}</span>
                            <span class="px-2 py-1 bg-white rounded text-[10px] font-bold text-gray-500 border border-blue-100">${orden.vehiculo?.color || 'N/A'}</span>
                        </div>
                    </div>
                </section>

                <section>
                    <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-2">
                         <i class="fas fa-info-circle"></i> Datos Recepción
                    </h4>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Km</span>
                            <span class="font-black text-gray-700">${new Intl.NumberFormat().format(orden.kilometraje_entrada)}</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-center">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Combustible</span>
                            <span class="font-black text-gray-700">${orden.nivel_combustible}</span>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Columna 2: Falla y Reporte -->
            <div class="md:col-span-2 space-y-6">
                <section>
                    <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-2">
                         <i class="fas fa-comment-medical text-red-500"></i> Motivo / Falla Reportada
                    </h4>
                    <div class="bg-red-50/30 p-4 rounded-2xl border border-red-100 min-h-[100px]">
                        <p class="text-gray-700 whitespace-pre-wrap leading-relaxed">${orden.falla_cliente || 'No especificado'}</p>
                    </div>
                </section>

                <section>
                    <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-2">
                         <i class="fas fa-camera text-blue-500"></i> Fotos de Recepción
                    </h4>
                    <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                        ${orden.archivos && orden.archivos.length > 0
            ? orden.archivos.map(f => `
                                <div class="min-w-[150px] h-24 bg-gray-100 rounded-xl overflow-hidden shadow-sm border border-gray-200">
                                    <img src="${window.location.origin + '/' + f.url}" class="w-full h-full object-cover">
                                </div>
                            `).join('')
            : '<p class="text-xs text-gray-400 font-bold italic p-4 bg-gray-50 w-full rounded-xl border border-dashed text-center">Sin fotografías registradas</p>'
        }
                    </div>
                </section>

                ${orden.danos_imagen_url ? `
                    <section>
                        <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest mb-3 flex items-center gap-2">
                            <i class="fas fa-car-crash text-orange-500"></i> Mapa de Daños
                        </h4>
                        <div class="bg-gray-900 rounded-2xl overflow-hidden border border-gray-800 max-h-[250px] flex items-center justify-center text-center">
                            <img src="${window.location.origin + '/' + orden.danos_imagen_url}" class="max-w-full max-h-full object-contain">
                        </div>
                    </section>
                ` : ''}
            </div>
        </div>

        <!-- Footer Modal -->
        <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-between items-center italic text-[10px] font-bold text-gray-400 uppercase tracking-widest">
            <span>Recibido por: ${orden.receptor?.name || 'Sistema'}</span>
            <span>Fecha: ${new Date(orden.fecha_recepcion).toLocaleString()}</span>
        </div>
    `;
}

/**
 * Crea el HTML para una tarjeta de cita pendiente
 */
function createPendingCard(cita, createUrlBase) {
    const createUrl = `${createUrlBase}?cita_id=${cita.id}`;

    return `
        <div class="bg-white p-4 rounded-xl border-l-[6px] border-green-500 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 group hover:bg-green-50/30 transition-all border border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center text-green-600 font-black text-xl shadow-inner group-hover:scale-110 transition-transform">
                    <i class="fas fa-car-side"></i>
                </div>
                <div>
                    <h4 class="font-black text-gray-800 text-lg leading-none mb-1 uppercase tracking-tighter">${cita.cliente?.nombre_completo || 'Cliente'}</h4>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-tight">
                        ${cita.vehiculo ? `<span class="text-green-600 font-black">${cita.vehiculo.placa}</span> - ${cita.vehiculo.marca?.nombre} ${cita.vehiculo.modelo?.nombre}` : 'Vehículo no identificado'}
                    </p>
                    <p class="text-[10px] text-blue-600 mt-1 font-black uppercase tracking-widest bg-blue-50 inline-block px-2 py-0.5 rounded border border-blue-100">
                        <i class="fas fa-store mr-1"></i> ${cita.sucursal?.nombre || 'General'}
                    </p>
                    <p class="text-[10px] text-gray-400 mt-1 font-bold uppercase">
                        <i class="far fa-clock mr-1"></i> Llegó: ${new Date(cita.updated_at).toLocaleString()}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <a href="${createUrl}" class="btn bg-green-600 hover:bg-green-700 text-white font-black py-2 px-8 rounded-xl shadow-lg shadow-green-600/20 border-none flex items-center gap-3 w-full md:w-auto justify-center lowercase tracking-widest text-xs">
                    <span>EMPEZAR ORDEN</span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    `;
}

/**
 * Crea el HTML para una tarjeta de orden activa
 */
function createOrderCard(orden) {
    const statusColors = {
        'abierta': 'bg-blue-100 text-blue-700 border-blue-200',
        'en_proceso': 'bg-purple-100 text-purple-700 border-purple-200',
        'espera_repuesto': 'bg-orange-100 text-orange-700 border-orange-200',
        'detenida': 'bg-orange-100 text-orange-700 border-orange-200',
        'finalizada': 'bg-green-100 text-green-700 border-green-200',
        'entregada': 'bg-gray-100 text-gray-700 border-gray-300'
    };
    const statusLabels = {
        'abierta': 'Abierta',
        'en_proceso': 'En Proceso',
        'espera_repuesto': 'Espera Repuesto',
        'detenida': 'Detenida',
        'finalizada': 'Finalizada',
        'entregada': 'Entregada'
    };

    const badge = statusColors[orden.estado] || 'bg-gray-100 text-gray-600';
    const label = statusLabels[orden.estado] || orden.estado;

    const editUrl = window.routes.create.replace('/create', `/${orden.id}/edit`);
    const printUrl = window.routes.print.replace('ID_PLACEHOLDER', orden.id);

    const showUrl = window.routes.show.replace('ID_PLACEHOLDER', orden.id);

    return `
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-blue-200 transition-all relative group overflow-hidden">
            <!-- Background Accent -->
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-gray-50 rounded-full group-hover:bg-blue-50 transition-colors z-0"></div>

            <div class="relative z-10">
                <a href="${showUrl}" class="block">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block mb-1">Orden #${orden.codigo_orden}</span>
                            <h4 class="font-black text-gray-800 text-xl tracking-tighter leading-none group-hover:text-blue-600 transition-colors">${orden.cliente?.nombre_completo || 'Cliente'}</h4>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm ${badge}">
                            ${label}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-bold mb-5">
                        <div class="flex items-center gap-2 text-gray-500 bg-gray-50 p-2 rounded-xl border border-gray-100 uppercase tracking-tighter">
                            <i class="fas fa-car text-blue-500"></i>
                            <span class="truncate">${orden.vehiculo ? `<span class="text-gray-800 font-black">${orden.vehiculo.placa}</span> - ${orden.vehiculo.marca?.nombre}` : 'Vehículo'}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500 bg-gray-50 p-2 rounded-xl border border-gray-100 uppercase tracking-tighter">
                            <i class="fas fa-tachometer-alt text-purple-500"></i>
                             ${number_format(orden.kilometraje_entrada)} km
                        </div>
                        <div class="col-span-full flex items-center gap-2 text-blue-600 bg-blue-50/50 p-2 rounded-xl border border-blue-100 uppercase tracking-widest text-[9px] font-black">
                            <i class="fas fa-map-marker-alt"></i>
                            Sucursal: ${orden.sucursal?.nombre || 'General'}
                        </div>
                    </div>
                </a>

                <div class="border-t border-gray-50 pt-4 flex justify-between items-center">
                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Recibe: ${orden.receptor?.name}</span>
                    <div class="flex gap-2">
                        <a href="${showUrl}" class="h-9 w-9 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Planificar / Ver Detalles">
                            <i class="fas fa-eye text-sm"></i>
                        </a>
                        <a href="${editUrl}" class="h-9 w-9 bg-green-50 text-green-600 rounded-xl flex items-center justify-center hover:bg-green-600 hover:text-white transition-all shadow-sm" title="Editar Recepción">
                            <i class="fas fa-edit text-sm"></i>
                        </a>
                        <a href="${printUrl}" target="_blank" class="h-9 w-9 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center hover:bg-purple-600 hover:text-white transition-all shadow-sm" title="Imprimir Recepción">
                            <i class="fas fa-print text-sm"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Helper function
function number_format(number) {
    return new Intl.NumberFormat().format(number);
}
