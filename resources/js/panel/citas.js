document.addEventListener('DOMContentLoaded', () => {
    // Cargar citas iniciales
    loadCitas();

    // Event Listener for Manual Modal Form
    const form = document.getElementById('crearCitaForm');
    if (form) form.addEventListener('submit', storeCita);

    // Check URL params for auto-open
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
        openManualCitaModal();
        // Limpiar URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

let currentFilter = 'all';

// ===== GLOBAL EXPORTS =====
window.loadCitas = loadCitas;
window.filterCitas = filterCitas;
window.openManualCitaModal = openManualCitaModal;
window.closeManualCitaModal = closeManualCitaModal;
window.debounceSearchClient = debounceSearchClient;
window.clearSelectedClient = clearSelectedClient;
window.openCitaModal = openCitaModal;
window.closeCitaModal = closeCitaModal;
window.updateStatus = updateStatus;
// Note: loadBrands, loadModelsByMarca, loadVersionsByModelo, checkMarcaManual, etc. 
// are assigned to window directly in their definitions or after.

// ===== MANUAL CREATE MODAL LOGIC =====
function openManualCitaModal() {
    const modal = document.getElementById('crearCitaModal');
    const form = document.querySelector('#crearCitaModal form');

    if (!modal) return;

    // Default Date Today
    const dateInput = document.getElementById('inputFecha');
    if (dateInput) dateInput.value = new Date().toISOString().split('T')[0];

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        form.classList.remove('translate-y-full', 'scale-95');
        form.classList.add('translate-y-0', 'scale-100');
    }, 10);

    setTimeout(() => {
        const input = document.getElementById('searchClientInput');
        if (input) input.focus();
    }, 300);
}

function closeManualCitaModal() {
    const modal = document.getElementById('crearCitaModal');
    const form = document.querySelector('#crearCitaModal form');

    if (!modal) return;

    modal.classList.add('opacity-0');
    form.classList.add('translate-y-full', 'scale-95');
    form.classList.remove('translate-y-0', 'scale-100');

    setTimeout(() => modal.classList.add('hidden'), 300);

    // Reset Form
    document.getElementById('crearCitaForm').reset();
    clearSelectedClient();
}

// ===== LOGICA CREACION CITA (MODAL) =====

// Alternar entre pestañas
window.setCreationMode = function (mode) {
    // Buttons UI
    const btnBuscar = document.getElementById('btnModeBuscar');
    const btnNuevo = document.getElementById('btnModeNuevo');
    const modoInput = document.getElementById('modoCreacion');

    modoInput.value = mode;

    if (mode === 'buscar') {
        btnBuscar.classList.replace('text-gray-500', 'text-gray-700');
        btnBuscar.classList.add('bg-white', 'shadow-sm', 'font-bold');
        btnBuscar.classList.remove('text-gray-500', 'hover:text-gray-700');

        btnNuevo.classList.remove('bg-white', 'shadow-sm', 'text-gray-700', 'font-bold');
        btnNuevo.classList.add('text-gray-500', 'hover:text-gray-700');

        // Sections
        document.getElementById('sectionBuscarCliente').classList.remove('hidden');
        document.getElementById('sectionNuevoCliente').classList.add('hidden');

        // Vehicle Section
        document.getElementById('vehiculoSelectContainer').classList.remove('hidden');
        document.getElementById('vehiculoNewContainer').classList.add('hidden');
    } else {
        btnNuevo.classList.replace('text-gray-500', 'text-gray-700');
        btnNuevo.classList.add('bg-white', 'shadow-sm', 'font-bold');
        btnNuevo.classList.remove('text-gray-500', 'hover:text-gray-700');

        btnBuscar.classList.remove('bg-white', 'shadow-sm', 'text-gray-700', 'font-bold');
        btnBuscar.classList.add('text-gray-500', 'hover:text-gray-700');

        // Sections
        document.getElementById('sectionBuscarCliente').classList.add('hidden');
        document.getElementById('sectionNuevoCliente').classList.remove('hidden');

        // Vehicle Section
        document.getElementById('vehiculoSelectContainer').classList.add('hidden');
        document.getElementById('vehiculoNewContainer').classList.remove('hidden');

        // Trigger load brands if empty
        const marcaSelect = document.getElementById('marcaNuevoSelect');
        if (marcaSelect && marcaSelect.options.length <= 1) {
            loadBrands();
        }
    }
}

// ===== MARCAS, MODELOS Y VERSIONES LOGIC =====

async function loadBrands() {
    const select = document.getElementById('marcaNuevoSelect');
    if (!select) return;

    try {
        const res = await fetch(window.APP_CONFIG.API_GET_BRANDS);
        const marcas = await res.json();

        select.innerHTML = '<option value="">Seleccione...</option>';
        marcas.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.nombre;
            select.appendChild(opt);
        });

        // Append Option 'OTRA' (Manual)
        const optManual = document.createElement('option');
        optManual.value = 'otro';
        optManual.textContent = '-- OTRA / MANUAL --';
        optManual.style.fontWeight = 'bold';
        select.appendChild(optManual);

    } catch (e) {
        console.error('Error loading brands', e);
        select.innerHTML = '<option value="">Error al cargar</option>';
    }
}

window.checkMarcaManual = function (select) {
    const input = document.getElementById('marcaNuevoInput');
    const modeloSelect = document.getElementById('modeloNuevoSelect');
    const versionSelect = document.getElementById('versionNuevoSelect');

    if (select.value === 'otro') {
        input.classList.remove('hidden');
        input.value = ''; // Clear for manual entry
        input.focus();

        // Reset and hide model select, show model input directly
        modeloSelect.disabled = true;
        modeloSelect.classList.add('hidden');
        document.getElementById('modeloNuevoInput').classList.remove('hidden');

        // Reset and hide version select, show version input directly
        versionSelect.disabled = true;
        versionSelect.classList.add('hidden');
        document.getElementById('versionNuevoInput').classList.remove('hidden');
    } else {
        input.classList.add('hidden');
        // If a valid brand is selected, set the input value to the name for the backend
        if (select.value) {
            input.value = select.options[select.selectedIndex].text;
            loadModelsByMarca(select.value);
            modeloSelect.classList.remove('hidden');
            document.getElementById('modeloNuevoInput').classList.add('hidden');
        } else {
            input.value = '';
            modeloSelect.innerHTML = '<option value="">Seleccione Marca...</option>';
            modeloSelect.disabled = true;
            versionSelect.innerHTML = '<option value="">Seleccione Modelo...</option>';
            versionSelect.disabled = true;
        }
    }
}

async function loadModelsByMarca(marcaId) {
    const select = document.getElementById('modeloNuevoSelect');
    if (!select) return;

    select.disabled = true;
    select.innerHTML = '<option>Cargando...</option>';

    try {
        const res = await fetch(`${window.APP_CONFIG.API_GET_MODELS}/${marcaId}`);
        const modelos = await res.json();

        select.innerHTML = '<option value="">Seleccione...</option>';
        modelos.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.id;
            opt.textContent = m.nombre;
            select.appendChild(opt);
        });

        // Append Option 'OTRO'
        const optManual = document.createElement('option');
        optManual.value = 'otro';
        optManual.textContent = '-- OTRO / MANUAL --';
        optManual.style.fontWeight = 'bold';
        select.appendChild(optManual);

        select.disabled = false;

    } catch (e) {
        select.innerHTML = '<option>Error</option>';
    }
}

window.checkModeloManual = function (select) {
    const input = document.getElementById('modeloNuevoInput');
    const versionSelect = document.getElementById('versionNuevoSelect');

    if (select.value === 'otro') {
        input.classList.remove('hidden');
        input.value = ''; // Clear for manual entry
        input.focus();

        // Reset and hide version select, show version input directly
        versionSelect.disabled = true;
        versionSelect.classList.add('hidden');
        document.getElementById('versionNuevoInput').classList.remove('hidden');
    } else {
        input.classList.add('hidden');
        // If a valid model is selected, set the input value to the name for the backend
        if (select.value) {
            input.value = select.options[select.selectedIndex].text;
            loadVersionsByModelo(select.value);
            versionSelect.classList.remove('hidden');
            document.getElementById('versionNuevoInput').classList.add('hidden');
        } else {
            input.value = '';
            versionSelect.innerHTML = '<option value="">Seleccione Modelo...</option>';
            versionSelect.disabled = true;
        }
    }
}

async function loadVersionsByModelo(modeloId) {
    const select = document.getElementById('versionNuevoSelect');
    if (!select) return;

    select.disabled = true;
    select.innerHTML = '<option>Cargando...</option>';

    try {
        const res = await fetch(`${window.APP_CONFIG.API_GET_VERSIONS}/${modeloId}`);
        const versiones = await res.json();

        select.innerHTML = '<option value="">Seleccione...</option>';
        versiones.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = v.nombre;
            select.appendChild(opt);
        });

        // Append Option 'OTRO'
        const optManual = document.createElement('option');
        optManual.value = 'otro';
        optManual.textContent = '-- OTRA / MANUAL --';
        optManual.style.fontWeight = 'bold';
        select.appendChild(optManual);

        select.disabled = false;

    } catch (e) {
        select.innerHTML = '<option>Error</option>';
    }
}

window.checkVersionManual = function (select) {
    const input = document.getElementById('versionNuevoInput');
    if (select.value === 'otro') {
        input.classList.remove('hidden');
        input.value = ''; // Clear for manual entry
        input.focus();
    } else {
        input.classList.add('hidden');
        // If a valid version is selected, set the input value to the name for the backend
        if (select.value) {
            input.value = select.options[select.selectedIndex].text;
        } else {
            input.value = '';
        }
    }
}

// Client Search Logic...
let searchTimeout;
function debounceSearchClient() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchClient, 300);
}

// ... (searchClient and selectClient remain same) ...
async function searchClient() {
    const term = document.getElementById('searchClientInput').value;
    const resultsContainer = document.getElementById('clientSearchResults');

    if (term.length < 2) {
        resultsContainer.classList.add('hidden');
        return;
    }

    try {
        const response = await fetch(`${window.APP_CONFIG.API_SEARCH_CLIENTS}?term=${term}`);
        const clients = await response.json();

        resultsContainer.innerHTML = '';

        if (clients.length === 0) {
            resultsContainer.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">No se encontraron clientes</div>';
        } else {
            clients.forEach(client => {
                const div = document.createElement('div');
                div.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-0 transition-colors';
                div.innerHTML = `
                    <p class="font-bold text-gray-800 text-sm">${client.nombre_completo}</p>
                    <p class="text-xs text-gray-500">${client.telefono} | ${client.email || ''}</p>
                `;
                div.onclick = () => selectClient(client);
                resultsContainer.appendChild(div);
            });
        }

        resultsContainer.classList.remove('hidden');
    } catch (e) {
        console.error(e);
    }
}

function selectClient(client) {
    // UI Update
    document.getElementById('searchClientInput').value = '';
    document.getElementById('clientSearchResults').classList.add('hidden');
    document.getElementById('searchClientInput').parentElement.classList.add('hidden'); // Hide Search Box

    document.getElementById('selectedClientCard').classList.remove('hidden');
    document.getElementById('selectedClientName').textContent = client.nombre_completo;
    document.getElementById('selectedClientPhone').textContent = client.telefono;
    document.getElementById('cliente_id').value = client.id;

    // Load Vehicles
    loadClientVehicles(client.id);
}

function clearSelectedClient() {
    document.getElementById('selectedClientCard').classList.add('hidden');
    document.getElementById('searchClientInput').parentElement.classList.remove('hidden');
    document.getElementById('searchClientInput').value = '';
    document.getElementById('cliente_id').value = '';

    // Reset Vehicles
    const select = document.getElementById('vehiculoSelect');
    select.innerHTML = '<option value="">Primero selecciona un cliente...</option>';
    select.disabled = true;
}

async function loadClientVehicles(clienteId) {
    const select = document.getElementById('vehiculoSelect');
    select.disabled = true;
    select.innerHTML = '<option>Cargando vehículos...</option>';

    try {
        const response = await fetch(`${window.APP_CONFIG.API_GET_VEHICLES}/${clienteId}`);
        const vehiculos = await response.json();

        select.innerHTML = '';

        if (vehiculos.length === 0) {
            select.innerHTML = '<option value="">Este cliente no tiene vehículos</option>';
        } else {
            vehiculos.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.texto;
                select.appendChild(opt);
            });
            select.disabled = false;
        }

    } catch (e) {
        select.innerHTML = '<option>Error al cargar</option>';
    }
}

// Store Function
async function storeCita(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const modo = data.modo_creacion;

    // Validation
    if (modo === 'buscar') {
        if (!data.cliente_id) {
            Swal.fire('Error', 'Debes seleccionar un cliente de la lista.', 'warning');
            return;
        }
        if (!data.vehiculo_id) {
            Swal.fire('Error', 'Debes seleccionar un vehículo.', 'warning');
            return;
        }
    } else {
        // Validación Nuevo
        if (!data.nombre_nuevo || !data.telefono_nuevo || !data.placa_nuevo || !data.marca_nuevo) {
            Swal.fire('Atención', 'Por favor completa al menos Nombre, Teléfono, Placa y Marca.', 'warning');
            return;
        }
    }

    try {
        const btn = e.target.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.innerText = 'Guardando...';
        btn.disabled = true;

        const response = await fetch(window.APP_CONFIG.API_UPDATE, { // POST creates new
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.APP_CONFIG.CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire({
                title: 'Éxito',
                text: 'Cita y datos registrados correctamente',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
            closeManualCitaModal();
            loadCitas(); // Reload list
        } else {
            Swal.fire('Error', result.message, 'error');
        }

        btn.innerText = originalText;
        btn.disabled = false;

    } catch (e) {
        Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
    }
}

async function loadCitas() {
    const container = document.getElementById('citasContainer');
    const dateFilter = document.getElementById('dateFilter').value;

    // UI Loading
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-64 text-gray-400">
            <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i>
            <p class="animate-pulse font-medium">Sincronizando agenda...</p>
        </div>
    `;

    try {
        let url = `${window.APP_CONFIG.API_CITAS}?estado=${currentFilter === 'all' ? '' : currentFilter}`;

        // Si hay filtro de fecha, usarlo
        if (dateFilter) {
            // Un truco simple para filtrar por día es mandar start y end como el mismo día (inicio y fin)
            url += `&start=${dateFilter} 00:00:00&end=${dateFilter} 23:59:59`;

            // Para evitar problemas de zona horaria al mostrar el título de la agenda
            const [y, m, d] = dateFilter.split('-');
            const displayDate = new Date(y, m - 1, d);
            document.getElementById('agendaTitle').textContent = `Agenda del ${displayDate.toLocaleDateString()}`;
        } else {
            document.getElementById('agendaTitle').textContent = 'Agenda General';
        }

        const response = await fetch(url);
        const citas = await response.json();

        updateCounters(citas);
        renderCitas(citas);

    } catch (error) {
        console.error(error);
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center h-64 text-red-400">
                <i class="fas fa-exclamation-circle text-3xl mb-3"></i>
                <p>Error al cargar datos</p>
                <button onclick="loadCitas()" class="mt-4 px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-bold hover:bg-red-100 transition">Reintentar</button>
            </div>
        `;
    }
}

function renderCitas(citas) {
    const container = document.getElementById('citasContainer');
    container.innerHTML = '';

    if (citas.length === 0) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-gray-400 py-10">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="far fa-calendar-times text-3xl text-gray-300"></i>
                </div>
                <p class="font-medium text-lg text-gray-500">No hay citas programadas</p>
                <p class="text-sm">Intenta cambiar los filtros o la fecha</p>
            </div>
        `;
        return;
    }

    // Agrupar por fecha para mejor visualización
    const grouped = groupByDate(citas);

    Object.keys(grouped).forEach(date => {
        // Humanize Date Header
        const dateObj = new Date(date);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateString = dateObj.toLocaleDateString('es-ES', options);

        const groupDiv = document.createElement('div');
        groupDiv.className = 'mb-6';
        groupDiv.innerHTML = `
            <div class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur-sm py-2 px-4 mb-2 border-b border-gray-200 text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center gap-2">
                <i class="far fa-calendar"></i> ${dateString}
            </div>
        `;

        grouped[date].forEach(cita => {
            const card = createCitaCard(cita);
            groupDiv.appendChild(card);
        });

        container.appendChild(groupDiv);
    });
}

function createCitaCard(cita) {
    const el = document.createElement('div');
    // Estados colores
    const statusColors = {
        'pendiente': 'border-yellow-400 bg-yellow-50/10 hover:border-yellow-500',
        'confirmada': 'border-blue-500 bg-blue-50/10 hover:border-blue-600',
        'concretada': 'border-green-500 bg-green-50/10 hover:border-green-600',
        'cancelada': 'border-gray-300 bg-gray-50 opacity-60',
        'no_asistio': 'border-red-400 bg-red-50/10'
    };

    // Hora formateada
    const time = new Date(cita.start).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    el.className = `bg-white p-4 rounded-xl border-l-[6px] shadow-sm hover:shadow-md transition-all cursor-pointer group mb-3 relative flex gap-4 ${statusColors[cita.estado] || 'border-gray-200'}`;

    el.onclick = () => openCitaModal(cita);

    el.innerHTML = `
        <div class="flex flex-col items-center justify-center min-w-[60px] border-r border-gray-100 pr-4">
            <span class="text-xl font-black text-gray-800">${time}</span>
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">HRS</span>
        </div>
        
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-start">
                <h4 class="font-bold text-gray-800 truncate text-lg group-hover:text-blue-600 transition-colors">${cita.cliente}</h4>
                ${getStatusBadge(cita.estado)}
            </div>
            
            <p class="text-sm text-gray-600 font-medium flex items-center gap-1 mt-1">
                <i class="fas fa-car text-gray-400 text-xs"></i> 
                ${cita.vehiculo}
            </p>
            
            <p class="text-xs text-gray-400 mt-2 truncate bg-gray-50 p-1.5 rounded inline-block max-w-full">
                <i class="fas fa-wrench text-[10px] mr-1"></i> ${cita.description}
            </p>
        </div>
        
        <div class="flex items-center text-gray-300 group-hover:text-blue-500 transition-colors pl-2">
            <i class="fas fa-chevron-right"></i>
        </div>
    `;

    return el;
}

// ===== MODAL LOGIC =====
let currentCitaId = null;

function openCitaModal(cita) {
    currentCitaId = cita.id;
    const modal = document.getElementById('citaModal');
    const content = document.getElementById('citaModalContent');

    // Populate Data
    document.getElementById('modalClienteName').textContent = cita.cliente;
    document.getElementById('modalVehiculoInfo').textContent = cita.vehiculo;
    document.getElementById('modalFecha').textContent = new Date(cita.start).toLocaleString([], { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' });
    document.getElementById('modalMotivo').textContent = cita.description;

    const phoneLink = document.getElementById('modalPhoneLink');
    phoneLink.textContent = cita.telefono;
    phoneLink.href = `tel:${cita.telefono}`;

    // Badge
    const badgeContainer = document.getElementById('modalStatusBadge');
    badgeContainer.outerHTML = getStatusBadge(cita.estado, 'modalStatusBadge', true);

    // Actions depending on Status
    const actionsContainer = document.getElementById('modalActions');
    actionsContainer.innerHTML = '';

    if (cita.estado === 'pendiente') {
        actionsContainer.innerHTML = `
            <button onclick="updateStatus('confirmada')" class="btn bg-blue-600 hover:bg-blue-700 text-white w-full py-3 rounded-xl font-bold shadow-lg shadow-blue-500/30">
                <i class="fas fa-check mr-2"></i> Confirmar Cita
            </button>
            <button onclick="updateStatus('cancelada')" class="btn bg-gray-100 hover:bg-red-50 text-gray-600 hover:text-red-600 w-full py-3 rounded-xl font-bold">
                <i class="fas fa-times mr-2"></i> Rechazar
            </button>
        `;
    } else if (cita.estado === 'confirmada') {
        actionsContainer.innerHTML = `
            <button onclick="updateStatus('concretada')" class="btn bg-green-600 hover:bg-green-700 text-white col-span-2 py-3 rounded-xl font-bold shadow-lg shadow-green-500/30 flex items-center justify-center gap-2">
                <i class="fas fa-file-signature text-lg"></i> 
                <span>Cliente Llegó (Crear Orden)</span>
            </button>
             <button onclick="updateStatus('no_asistio')" class="btn bg-red-50 hover:bg-red-100 text-red-500 w-full py-3 rounded-xl font-bold text-sm">
                No Asistió
            </button>
             <button onclick="updateStatus('cancelada')" class="btn bg-gray-50 hover:bg-gray-100 text-gray-500 w-full py-3 rounded-xl font-bold text-sm">
                Cancelar
            </button>
        `;
    } else if (cita.estado === 'concretada') {
        actionsContainer.innerHTML = `
            <div class="col-span-2 text-center p-3 bg-green-50 rounded-xl border border-green-100 text-green-700 font-bold">
                <i class="fas fa-check-circle mb-1 text-2xl"></i><br>
                Vehículo Recibido
            </div>
        `;
    }

    // Show
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function closeCitaModal() {
    const modal = document.getElementById('citaModal');
    const content = document.getElementById('citaModalContent');

    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');

    setTimeout(() => modal.classList.add('hidden'), 300);
}

// ===== ACTIONS =====
async function updateStatus(newStatus) {
    if (!currentCitaId) return;

    // Confirmación para acciones destructivas
    if (newStatus === 'cancelada' || newStatus === 'no_asistio') {
        const confirm = await Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción cambiará el estado de la cita.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar',
            cancelButtonText: 'Cancelar'
        });
        if (!confirm.isConfirmed) return;
    }

    // Si es "concretada", idealmente redirigiríamos a la pantalla de "Crear Orden de Trabajo" pre-llenada.
    // Por ahora solo cambio el estado.

    try {
        const response = await fetch(`${window.APP_CONFIG.API_UPDATE}/${currentCitaId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.APP_CONFIG.CSRF_TOKEN
            },
            body: JSON.stringify({
                action: 'cambiar_estado',
                estado: newStatus
            })
        });

        const result = await response.json();

        if (result.success) {
            closeCitaModal();
            loadCitas();
            Swal.fire({
                title: 'Actualizado',
                text: 'El estado de la cita ha cambiado correctamente',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }

    } catch (e) {
        Swal.fire('Error', 'No se pudo actualizar la cita', 'error');
    }
}

function filterCitas(status) {
    // Update Active Button
    document.querySelectorAll('.filter-btn').forEach(btn => btn.className = btn.className.replace('bg-blue-50 text-blue-700 ring-1 ring-blue-200 active', '').trim());

    const activeBtn = document.querySelector(`.filter-btn[data-status="${status}"]`);
    if (activeBtn) {
        activeBtn.classList.add('bg-blue-50', 'text-blue-700', 'ring-1', 'ring-blue-200', 'active');
    }

    currentFilter = status;
    loadCitas();
}


// ===== HELPERS =====
function updateCounters(citas) {
    // Calcular en frontend para rapidez
    const today = new Date().toISOString().split('T')[0];

    // Count Today Pendientes
    const todayCount = citas.filter(c => c.start.startsWith(today) && c.estado !== 'cancelada').length;
    document.getElementById('countToday').innerText = todayCount;

    // Badges Sidebar
    const counts = citas.reduce((acc, curr) => {
        acc[curr.estado] = (acc[curr.estado] || 0) + 1;
        return acc;
    }, {});

    if (document.getElementById('badge-pendiente')) document.getElementById('badge-pendiente').innerText = counts['pendiente'] || 0;
    if (document.getElementById('badge-confirmada')) document.getElementById('badge-confirmada').innerText = counts['confirmada'] || 0;
    if (document.getElementById('badge-concretada')) document.getElementById('badge-concretada').innerText = counts['concretada'] || 0;
}

function groupByDate(citas) {
    return citas.reduce((groups, cita) => {
        const date = cita.start.split('T')[0];
        if (!groups[date]) {
            groups[date] = [];
        }
        groups[date].push(cita);
        return groups;
    }, {});
}

function getStatusBadge(status, id = '', large = false) {
    const config = {
        'pendiente': { color: 'text-yellow-700 bg-yellow-100 border-yellow-200', text: 'Pendiente', icon: 'fa-clock' },
        'confirmada': { color: 'text-blue-700 bg-blue-100 border-blue-200', text: 'Confirmada', icon: 'fa-thumbs-up' },
        'concretada': { color: 'text-green-700 bg-green-100 border-green-200', text: 'En Taller', icon: 'fa-check-circle' },
        'cancelada': { color: 'text-gray-500 bg-gray-100 border-gray-200', text: 'Cancelada', icon: 'fa-ban' },
        'no_asistio': { color: 'text-red-700 bg-red-100 border-red-200', text: 'No Asistió', icon: 'fa-user-slash' },
    };

    const style = config[status] || { color: 'text-gray-500', text: status };
    const sizeClasses = large ? 'px-3 py-1 text-sm' : 'px-2 py-0.5 text-[10px]';

    return `
        <span id="${id}" class="${sizeClasses} rounded font-bold uppercase tracking-wider border flex items-center gap-1.5 w-fit ${style.color}">
            <i class="fas ${style.icon}"></i> ${style.text}
        </span>
    `;
}
