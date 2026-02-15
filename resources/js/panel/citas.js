document.addEventListener('DOMContentLoaded', () => {
    // Set Default Week (Current Week)
    const today = new Date();
    const day = today.getDay(); // 0 (Sun) - 6 (Sat)

    // Calculate Monday
    const diff = today.getDate() - day + (day === 0 ? -6 : 1);
    const monday = new Date(today);
    monday.setDate(diff);

    // Calculate Sunday
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);

    // Helper for Local Date String (YYYY-MM-DD)
    const toLocal = (d) => new Date(d.getTime() - (d.getTimezoneOffset() * 60000)).toISOString().split('T')[0];

    const ds = document.getElementById('dateStart');
    const de = document.getElementById('dateEnd');

    if (ds && de) {
        ds.value = toLocal(monday);
        de.value = toLocal(sunday);
    }

    renderCalendar();
    // Cargar citas iniciales
    loadCitas();

    // Event Listener for Manual Modal Form
    const form = document.getElementById('crearCitaForm');
    if (form) form.addEventListener('submit', storeCita);

    // Check URL params for auto-open
    // Check URL params for auto-open
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'create') {
        openManualCitaModal();
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Init Calendar
    renderCalendar();
    fetchCalendarCounts();
});

let currentFilter = 'all';

// Calendar Variables
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
let calendarCounts = {}; // { '2026-02-10': 3 }

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
window.clearDateFilters = clearDateFilters;
window.prevMonth = prevMonth;
window.nextMonth = nextMonth;
window.selectCalendarDate = selectCalendarDate;

// ===== CALENDAR LOGIC =====
function prevMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    renderCalendar();
    fetchCalendarCounts();
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    renderCalendar();
    fetchCalendarCounts();
}

function renderCalendar() {
    const title = document.getElementById('miniCalendarTitle');
    const grid = document.getElementById('miniCalendarGrid');

    if (!title || !grid) return;

    // Set Title
    const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    title.textContent = `${months[currentMonth]} ${currentYear}`;

    // Logic
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    // Today's Date
    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    grid.innerHTML = '';

    // Empty cells
    for (let i = 0; i < firstDay; i++) {
        grid.innerHTML += `<div></div>`;
    }

    // Days
    for (let i = 1; i <= daysInMonth; i++) {
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        const hasAppt = calendarCounts[dateStr];
        let colorClass = 'bg-gray-50 text-gray-400 hover:bg-gray-100'; // Default

        if (hasAppt) {
            colorClass = 'bg-green-500 text-white shadow-sm shadow-green-200 hover:bg-green-600 font-bold';
        } else {
            colorClass = 'bg-red-50 text-red-300 hover:bg-red-100';
        }

        // Highlight Today
        if (dateStr === todayStr) {
            if (!hasAppt) { // If no appointment, make it distinct but not green
                colorClass = 'bg-orange-50 text-orange-600 font-black border-2 border-orange-400 z-10';
            } else {
                // Stronger Highlight for Today with Appt (Orange Ring to distinguish from Blue Selection)
                colorClass += ' ring-2 ring-orange-500 ring-offset-2 ring-offset-white font-black z-10 transform scale-105 shadow-md shadow-orange-200/50';
            }
        }

        // Highlight selected if matches filter (only if single day selected)
        const startFilterEl = document.getElementById('dateStart');
        const endFilterEl = document.getElementById('dateEnd');
        const startFilter = startFilterEl ? startFilterEl.value : '';
        const endFilter = endFilterEl ? endFilterEl.value : '';

        // Only highlight if start == end (Single Day View)
        if (startFilter && startFilter === endFilter && startFilter === dateStr) {
            colorClass += ' ring-2 ring-blue-600 ring-offset-1';
        }

        grid.innerHTML += `
            <button onclick="selectCalendarDate('${dateStr}')" class="w-full aspect-square flex items-center justify-center rounded-lg text-xs transition-all relative ${colorClass}">
                ${i}
                ${dateStr === todayStr ? '<span class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-current rounded-full"></span>' : ''}
            </button>
        `;
    }
}

async function fetchCalendarCounts() {
    try {
        const monthStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}`;
        const res = await fetch(`${window.APP_CONFIG.API_CALENDAR_COUNTS}?month=${monthStr}`); // New API
        const data = await res.json();

        // Map data { "2026-02-10": { count: 3 } } -> Simple Key Bool or Count
        calendarCounts = {};
        for (const [date, info] of Object.entries(data)) {
            if (info.count > 0) calendarCounts[date] = true;
        }
        renderCalendar(); // Re-render with colors

    } catch (e) {
        console.error("Error fetching calendar counts", e);
    }
}

function selectCalendarDate(dateStr) {
    const startEl = document.getElementById('dateStart');
    const endEl = document.getElementById('dateEnd');

    if (startEl) startEl.value = dateStr;
    if (endEl) endEl.value = dateStr; // Single day filter

    // Force 'Ver Todas' (All Statuses) when selecting a calendar day
    // This decouples the calendar selection from existing status filters
    filterCitas('all');
    renderCalendar();
}

function clearDateFilters() {
    const startEl = document.getElementById('dateStart');
    const endEl = document.getElementById('dateEnd');

    if (startEl) startEl.value = '';
    if (endEl) endEl.value = '';

    loadCitas();
    renderCalendar();
}

// ===== MANUAL CREATE MODAL LOGIC =====
function openManualCitaModal() {
    const modal = document.getElementById('crearCitaModal');
    const form = document.querySelector('#crearCitaModal form');

    if (!modal) return;

    // Default Date Today
    const dateInput = document.getElementById('inputFecha');
    if (dateInput) dateInput.value = new Date().toISOString().split('T')[0];

    // Reset Vehicle UI to Default State (Hidden New Form)
    const vehSelectContainer = document.getElementById('vehiculoSelectContainer');
    const vehNewContainer = document.getElementById('vehiculoNewContainer');
    const btnToggle = document.getElementById('btnToggleNewVehicle');
    const vehSelect = document.getElementById('vehiculoSelect');

    if (vehSelectContainer) vehSelectContainer.classList.remove('hidden');
    if (vehNewContainer) vehNewContainer.classList.add('hidden');

    if (btnToggle) {
        btnToggle.innerHTML = '<i class="fas fa-plus"></i> Nuevo Vehículo';
        btnToggle.classList.remove('text-red-500');
        btnToggle.classList.add('text-blue-600');
        btnToggle.classList.add('hidden'); // Hide until client selected
    }

    if (vehSelect) {
        vehSelect.value = '';
        if (vehSelect.dataset) vehSelect.dataset.previousValue = '';
    }

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
    const formEl = document.getElementById('crearCitaForm');
    if (formEl) formEl.reset();
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

// Client Search Logic
let searchTimeout;
function debounceSearchClient() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(searchClient, 300);
}

async function searchClient() {
    const input = document.getElementById('searchClientInput');
    const resultsContainer = document.getElementById('clientSearchResults');

    if (!input || !resultsContainer) return;

    const term = input.value;

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
    const input = document.getElementById('searchClientInput');
    const resultsContainer = document.getElementById('clientSearchResults');
    const card = document.getElementById('selectedClientCard');
    const nameEl = document.getElementById('selectedClientName');
    const phoneEl = document.getElementById('selectedClientPhone');
    const idInput = document.getElementById('cliente_id');

    if (input) input.value = '';
    if (resultsContainer) resultsContainer.classList.add('hidden');
    if (input && input.parentElement) input.parentElement.classList.add('hidden'); // Hide Search Box

    if (card) card.classList.remove('hidden');
    if (nameEl) nameEl.textContent = client.nombre_completo;
    if (phoneEl) phoneEl.textContent = client.telefono;
    if (idInput) idInput.value = client.id;

    // Load Vehicles
    loadClientVehicles(client.id);
}

function clearSelectedClient() {
    const card = document.getElementById('selectedClientCard');
    const input = document.getElementById('searchClientInput');
    const idInput = document.getElementById('cliente_id');

    if (card) card.classList.add('hidden');
    if (input && input.parentElement) input.parentElement.classList.remove('hidden');
    if (input) input.value = '';
    if (idInput) idInput.value = '';

    // Reset Vehicles
    const select = document.getElementById('vehiculoSelect');
    if (select) {
        select.innerHTML = '<option value="">Primero selecciona un cliente...</option>';
        select.disabled = true;
    }
}

async function loadClientVehicles(clienteId) {
    const select = document.getElementById('vehiculoSelect');
    if (!select) return;

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
        }

        // Add hidden option for triggering new mode logic
        const newOpt = document.createElement('option');
        newOpt.value = 'new_vehicle';
        newOpt.textContent = 'NUEVO';
        newOpt.hidden = true;
        select.appendChild(newOpt);

        select.disabled = false;

        // Show the toggle button
        const btnToggle = document.getElementById('btnToggleNewVehicle');
        if (btnToggle) btnToggle.classList.remove('hidden');

    } catch (e) {
        select.innerHTML = '<option>Error al cargar</option>';
    }
}

// TOGGLE NEW VEHICLE MODE
function toggleNewVehicleMode() {
    const selectContainer = document.getElementById('vehiculoSelectContainer');
    const newContainer = document.getElementById('vehiculoNewContainer');
    const btn = document.getElementById('btnToggleNewVehicle');
    const select = document.getElementById('vehiculoSelect');

    if (!selectContainer || !newContainer || !btn || !select) return;

    const isShowingSelect = !selectContainer.classList.contains('hidden');

    if (isShowingSelect) {
        // Save current selection before switching
        select.dataset.previousValue = select.value;

        // Switch to NEW Mode
        selectContainer.classList.add('hidden');
        newContainer.classList.remove('hidden');

        btn.innerHTML = '<i class="fas fa-undo"></i> Cancelar / Seleccionar Existente';
        btn.classList.add('text-red-500');
        btn.classList.remove('text-blue-600');

        // Set specific value to trigger backend logic
        select.value = 'new_vehicle';
        loadBrands();

    } else {
        // Switch back to SELECT Mode
        selectContainer.classList.remove('hidden');
        newContainer.classList.add('hidden');

        btn.innerHTML = '<i class="fas fa-plus"></i> Nuevo Vehículo';
        btn.classList.remove('text-red-500');
        btn.classList.add('text-blue-600');

        // Restore previous selection if it wasn't 'new_vehicle'
        const prev = select.dataset.previousValue;
        if (prev && prev !== 'new_vehicle') {
            select.value = prev;
        } else {
            select.value = ''; // Reset if no valid previous
        }
    }
}
window.toggleNewVehicleMode = toggleNewVehicleMode;

// Store Function
async function storeCita(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const modo = data.modo_creacion;

    // Validation
    if (modo === 'buscar') {
        if (!data.cliente_id) {
            Swal.fire({
                title: 'Error',
                text: 'Debes seleccionar un cliente de la lista.',
                icon: 'warning',
                showConfirmButton: true,
                confirmButtonText: 'Entendido',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors'
                }
            });
            return;
        }

        // Validation for new vehicle mode
        if (data.vehiculo_id === 'new_vehicle') {
            if (!data.marca_nuevo) {
                Swal.fire({
                    title: 'Atención',
                    text: 'Debes ingresar la marca del nuevo vehículo.',
                    icon: 'warning',
                    showConfirmButton: true,
                    confirmButtonText: 'Entendido',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors'
                    }
                });
                return;
            }
        }
        // Standard Validation
        else if (!data.vehiculo_id) {
            Swal.fire({
                title: 'Error',
                text: 'Debes seleccionar un vehículo.',
                icon: 'warning',
                showConfirmButton: true,
                confirmButtonText: 'Entendido',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors'
                }
            });
            return;
        }
    } else {
        // Validación Nuevo (Placa y Año ahora opcionales)
        if (!data.nombre_nuevo || !data.telefono_nuevo || !data.marca_nuevo) {
            Swal.fire({
                title: 'Atención',
                text: 'Por favor completa al menos Nombre, Teléfono y Marca.',
                icon: 'warning',
                showConfirmButton: true,
                confirmButtonText: 'Entendido',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-colors'
                }
            });
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

// IMPORTANT: Updated loadCitas
async function loadCitas() {
    const container = document.getElementById('citasContainer');
    if (!container) return; // Exit if container doesn't exist

    const startEl = document.getElementById('dateStart');
    const endEl = document.getElementById('dateEnd');

    // Safely get values
    const start = startEl ? startEl.value : '';
    const end = endEl ? endEl.value : '';

    // UI Loading
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-64 text-gray-400">
            <i class="fas fa-circle-notch fa-spin text-3xl mb-3 text-blue-500"></i>
            <p class="animate-pulse font-medium">Sincronizando agenda...</p>
        </div>
    `;

    try {
        let url = `${window.APP_CONFIG.API_CITAS}?estado=${currentFilter}`;

        if (start) url += `&start=${start}`;
        if (end) url += `&end=${end}`;

        // Set Agenda Title
        const titleEl = document.getElementById('agendaTitle');
        if (titleEl) {
            if (start && start === end) {
                const [y, m, d] = start.split('-');
                const displayDate = new Date(y, m - 1, d);
                titleEl.textContent = `Agenda: ${displayDate.toLocaleDateString()}`;
            } else if (start && end) {
                titleEl.textContent = `Agenda: ${start} al ${end}`;
            } else {
                titleEl.textContent = 'Agenda General';
            }
        }

        const response = await fetch(url);
        const data = await response.json();

        // Handle new response structure { citas: [], counts: {}, count_today: 5 }
        const citas = data.citas || [];
        const counts = data.counts || {};
        const countToday = data.count_today || 0;

        updateCounters(counts, countToday);
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

    // Email
    const emailLink = document.getElementById('modalEmailLink');
    if (emailLink) {
        emailLink.textContent = cita.email || 'Sin correo registrado';
        emailLink.href = cita.email ? `mailto:${cita.email}` : '#';
        // Style adjustments if empty
        if (!cita.email) {
            emailLink.classList.add('text-gray-300', 'italic');
            emailLink.classList.remove('text-blue-600', 'hover:underline');
        } else {
            emailLink.classList.remove('text-gray-300', 'italic');
            emailLink.classList.add('text-blue-600', 'hover:underline');
        }
    }

    // Communication Actions
    const cleanPhone = cita.telefono ? cita.telefono.replace(/\D/g, '') : '';
    const formattedDate = new Date(cita.start).toLocaleString('es-ES', { weekday: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });

    // 1. WhatsApp (Generic)
    const btnWhatsApp = document.getElementById('btnWhatsApp');
    const waMsg = encodeURIComponent(`Hola ${cita.cliente}, le escribimos de TallerPro sobre su vehículo ${cita.vehiculo}.`);
    // Use whatsapp:// protocol to open native app directly if installed, avoiding intermediate browser tab
    btnWhatsApp.onclick = (e) => {
        e.preventDefault();
        if (cleanPhone) {
            window.location.href = `whatsapp://send?phone=${cleanPhone}&text=${waMsg}`;
        }
    };
    if (!cleanPhone) btnWhatsApp.classList.add('opacity-50', 'pointer-events-none');
    else btnWhatsApp.classList.remove('opacity-50', 'pointer-events-none');

    // 2. Reminder (Predefined Message via WhatsApp)
    const btnReminder = document.getElementById('btnReminder');
    const reminderMsg = encodeURIComponent(`Hola ${cita.cliente}, le recordamos su cita en TallerPro para el vehículo ${cita.vehiculo} el día ${formattedDate}. Por favor confirme su asistencia. Le esperamos.`);

    btnReminder.onclick = () => {
        if (cleanPhone) {
            window.location.href = `whatsapp://send?phone=${cleanPhone}&text=${reminderMsg}`;
        } else {
            Swal.fire('Error', 'El cliente no tiene teléfono registrado', 'warning');
        }
    };

    // 3. Call
    const btnCall = document.getElementById('btnCall');
    btnCall.href = cleanPhone ? `tel:${cleanPhone}` : '#';
    if (!cleanPhone) btnCall.classList.add('opacity-50', 'pointer-events-none');
    else btnCall.classList.remove('opacity-50', 'pointer-events-none');

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
function updateCounters(counts, todayCount) {
    const todayCardEl = document.getElementById('countToday');
    if (todayCardEl) {
        todayCardEl.innerText = todayCount;
    }

    const badgePendiente = document.getElementById('badge-pendiente');
    const badgeConfirmada = document.getElementById('badge-confirmada');
    const badgeConcretada = document.getElementById('badge-concretada');
    const badgeNoAsistio = document.getElementById('badge-no_asistio');

    if (badgePendiente) badgePendiente.innerText = counts['pendiente'] || 0;
    if (badgeConfirmada) badgeConfirmada.innerText = counts['confirmada'] || 0;
    if (badgeConcretada) badgeConcretada.innerText = counts['concretada'] || 0;
    if (badgeNoAsistio) badgeNoAsistio.innerText = counts['no_asistio'] || 0;
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

window.setDateFilter = function (type) {
    const today = new Date();
    let start = new Date(today);
    let end = new Date(today);

    if (type === 'tomorrow') {
        start.setDate(today.getDate() + 1);
        end.setDate(today.getDate() + 1);
    }
    // If type is 'today', it remains today

    const fmt = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    if (document.getElementById('dateStart')) document.getElementById('dateStart').value = fmt(start);
    if (document.getElementById('dateEnd')) document.getElementById('dateEnd').value = fmt(end);

    window.loadCitas();
};
