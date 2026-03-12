document.addEventListener('DOMContentLoaded', function () {
    console.log('Sucursales JS loaded');
    loadSucursales();

    // Event Listeners para buscador
    document.getElementById('searchInput').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#sucursalesGrid > div');
        let hasResults = false;

        cards.forEach(card => {
            const nombre = card.querySelector('h4').textContent.toLowerCase();
            const direccion = card.querySelector('.fa-map-marker-alt').nextSibling.textContent.toLowerCase();

            if (nombre.includes(searchTerm) || direccion.includes(searchTerm)) {
                card.style.display = 'flex';
                hasResults = true;
            } else {
                card.style.display = 'none';
            }
        });

        const emptyState = document.getElementById('emptyState');
        if (!hasResults && cards.length > 0) {
            // Podríamos mostrar un estado vacío de "no busqueda"
        }
    });
});

let isEditing = false;
let currentId = null;

// Elementos del Modal
const modal = document.getElementById('sucursalModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalPanel = document.getElementById('modalPanel');
const modalTitle = document.getElementById('modalTitle');
const modalTitleText = modalTitle.querySelector('span');

// Inputs del Formulario
const nombreInput = document.getElementById('nombreSucursal');
const direccionInput = document.getElementById('direccionSucursal');
const telefonoInput = document.getElementById('telefonoSucursal');
const capacidadInput = document.getElementById('capacidadSucursal');
const ciudadInput = document.getElementById('ciudadSucursal');
const latitudInput = document.getElementById('latitudSucursal');
const longitudInput = document.getElementById('longitudSucursal');
const activaInput = document.getElementById('activaSucursal');

// Spans de Error
const errorNombre = document.getElementById('errorNombre');
const errorDireccion = document.getElementById('errorDireccion');
const errorTelefono = document.getElementById('errorTelefono');
const errorCapacidad = document.getElementById('errorCapacidad');

// Mapa
let map = null;
let marker = null;


async function loadSucursales() {
    const gridContainer = document.getElementById('sucursalesGrid');
    const emptyState = document.getElementById('emptyState');

    try {
        const response = await fetch(`${API_URL}/list`);
        const data = await response.json();

        gridContainer.innerHTML = ''; // Limpiar

        if (data.length === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
            return;
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
        }

        data.forEach((sucursal) => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl p-5 flex flex-col justify-between group hover:shadow-xl transition-all duration-300 border border-gray-100 relative overflow-hidden';

            // Escaping simple quotes for the onclick handler
            const safeNombre = sucursal.nombre.replace(/'/g, "\\'");
            const safeDireccion = sucursal.direccion.replace(/'/g, "\\'");

            card.innerHTML = `
                <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-blue-500 to-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                
                <div class="pl-2">
                    <div class="flex justify-between items-start mb-2">
                        <div class="h-10 w-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-lg">
                            ${sucursal.nombre.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex gap-2">
                             <button onclick="editSucursal(${JSON.stringify(sucursal).replace(/"/g, '&quot;')})" 
                                class="h-8 w-8 rounded-full bg-gray-100 text-blue-500 hover:bg-blue-500 hover:text-white transition-colors flex items-center justify-center" title="Editar">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <button onclick="deleteSucursal(${sucursal.id})" 
                                class="h-8 w-8 rounded-full bg-gray-100 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center" title="Eliminar">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <h4 class="text-gray-800 font-bold text-lg leading-tight mb-1 group-hover:text-blue-600 transition-colors">${sucursal.nombre}</h4>
                    
                    <div class="space-y-1 mt-3">
                        <p class="text-gray-500 text-sm flex items-start gap-2">
                            <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4 text-center"></i> 
                            ${sucursal.direccion}
                        </p>
                        <p class="text-gray-500 text-sm flex items-center gap-2">
                            <i class="fas fa-phone text-gray-400 w-4 text-center"></i> 
                            ${sucursal.telefono}
                        </p>
                        <p class="text-gray-500 text-sm flex items-center gap-2">
                            <i class="fas fa-warehouse text-gray-400 w-4 text-center"></i> 
                            <span class="font-medium text-gray-700">${sucursal.capacidad_bahias} Bahías</span>
                        </p>
                    </div>
                </div>
            `;
            gridContainer.appendChild(card);
        });

    } catch (error) {
        console.error('Error cargando sucursales:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar los datos.',
            background: '#1e293b',
            color: '#ffffff'
        });
    }
}

function openModal(isEdit = false) {
    modal.classList.remove('hidden');
    // Animate In
    setTimeout(() => {
        modalBackdrop.classList.remove('opacity-0');
        modalPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        modalPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        
        // Inicializar mapa y forzar recalculo de dimensiones pasado el tiempo de la transición CSS (aprox 300ms)
        setTimeout(() => {
            initMap(latitudInput.value, longitudInput.value);
            if (map) map.invalidateSize();
        }, 300);
    }, 50);

    if (!isEdit) {
        resetForm();
        isEditing = false;
        modalTitleText.textContent = 'Registro de Sucursal';
    }
}

function closeModal() {
    // Animate Out
    modalBackdrop.classList.add('opacity-0');
    modalPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
    modalPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
        resetForm();
    }, 300); // Wait for transition
}

function resetForm() {
    document.getElementById('sucursalForm').reset();
    isEditing = false;
    currentId = null;
    clearErrors();
}

function clearErrors() {
    [errorNombre, errorDireccion, errorTelefono, errorCapacidad].forEach(el => {
        el.textContent = '';
        el.classList.add('hidden');
    });
}

function editSucursal(sucursal) {
    isEditing = true;
    currentId = sucursal.id;

    nombreInput.value = sucursal.nombre;
    direccionInput.value = sucursal.direccion;
    telefonoInput.value = sucursal.telefono;
    capacidadInput.value = sucursal.capacidad_bahias;
    ciudadInput.value = sucursal.ciudad || '';
    activaInput.checked = sucursal.activa === 1 || sucursal.activa === true;
    latitudInput.value = sucursal.latitud || '';
    longitudInput.value = sucursal.longitud || '';

    modalTitleText.textContent = 'Editar Sucursal';

    openModal(true);
}

async function saveSucursal(e) {
    e.preventDefault();
    clearErrors();

    const data = {
        nombre: nombreInput.value,
        direccion: direccionInput.value,
        telefono: telefonoInput.value,
        capacidad_bahias: capacidadInput.value,
        ciudad: ciudadInput.value,
        latitud: latitudInput.value,
        longitud: longitudInput.value,
        activa: activaInput.checked ? 1 : 0
    };

    // Validacion simple lado cliente
    let hasError = false;
    if (!data.nombre.trim()) { showError(errorNombre, 'Nombre obligatorio'); hasError = true; }
    if (!data.direccion.trim()) { showError(errorDireccion, 'Dirección obligatoria'); hasError = true; }
    if (!data.telefono.trim()) { showError(errorTelefono, 'Teléfono obligatorio'); hasError = true; }
    if (!data.capacidad_bahias || data.capacidad_bahias < 1) { showError(errorCapacidad, 'Capacidad inválida'); hasError = true; }

    if (hasError) return;

    const method = isEditing ? 'PUT' : 'POST';
    const url = isEditing ? `${API_URL}/${currentId}` : API_URL;

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                if (result.errors.nombre) showError(errorNombre, result.errors.nombre[0]);
                if (result.errors.direccion) showError(errorDireccion, result.errors.direccion[0]);
                if (result.errors.telefono) showError(errorTelefono, result.errors.telefono[0]);
                if (result.errors.capacidad_bahias) showError(errorCapacidad, result.errors.capacidad_bahias[0]);
            } else {
                throw new Error(result.message || 'Error en el servidor');
            }
            return;
        }

        closeModal();
        loadSucursales();

        Swal.fire({
            icon: 'success',
            title: isEditing ? 'Actualizado' : 'Guardado',
            text: result.message,
            background: '#1e293b',
            color: '#ffffff',
            timer: 2000,
            showConfirmButton: false
        });

    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message,
            background: '#1e293b',
            color: '#ffffff'
        });
    }
}

function showError(element, message) {
    element.textContent = message;
    element.classList.remove('hidden');
}

function deleteSucursal(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "No podrás revertir esta acción",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-slate-700 rounded-xl',
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg mr-2',
            cancelButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg'
        },
        buttonsStyling: false
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'La sucursal ha sido eliminada.',
                        icon: 'success',
                        background: '#1e293b',
                        color: '#ffffff',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    loadSucursales();
                } else {
                    throw new Error(data.message || 'Error al eliminar');
                }

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    background: '#1a1a1a',
                    color: '#ffffff'
                });
            }
        }
    });
}

function initMap(lat, lng) {
    const defaultLat = 14.634915;
    const defaultLng = -90.515518;
    
    let centerLat = lat ? parseFloat(lat) : defaultLat;
    let centerLng = lng ? parseFloat(lng) : defaultLng;

    if (!map) {
        map = L.map('mapPicker').setView([centerLat, centerLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('click', function(e) {
            placeMarker(e.latlng.lat, e.latlng.lng);
        });
    } else {
        map.setView([centerLat, centerLng], 12);
    }

    if (lat && lng) {
        placeMarker(lat, lng);
    } else {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
    }
}

function placeMarker(lat, lng) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map);
    latitudInput.value = parseFloat(lat).toFixed(6);
    longitudInput.value = parseFloat(lng).toFixed(6);
}

async function searchAddress() {
    const query = document.getElementById('buscarDireccionMapa').value;
    const feedback = document.getElementById('searchResultFeedback');
    
    if (!query.trim()) {
        feedback.textContent = 'Ingresa una dirección primero.';
        feedback.classList.remove('hidden', 'text-green-500');
        feedback.classList.add('text-red-500');
        return;
    }

    feedback.textContent = 'Buscando...';
    feedback.classList.remove('hidden', 'text-red-500', 'text-green-500');
    feedback.classList.add('text-gray-500');

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`);
        const data = await response.json();

        if (data && data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lon = parseFloat(result.lon);

            map.setView([lat, lon], 16);
            placeMarker(lat, lon);

            feedback.textContent = 'Ubicación encontrada.';
            feedback.classList.remove('text-gray-500', 'text-red-500');
            feedback.classList.add('text-green-500');
        } else {
            feedback.textContent = 'No se encontraron resultados para esa dirección.';
            feedback.classList.remove('text-gray-500', 'text-green-500');
            feedback.classList.add('text-red-500');
        }
    } catch (error) {
        feedback.textContent = 'Error al conectar con el servidor de mapas.';
        feedback.classList.remove('text-gray-500', 'text-green-500');
        feedback.classList.add('text-red-500');
    }
}

// Exponer funciones al scope global
window.loadSucursales = loadSucursales;
window.openModal = openModal;
window.closeModal = closeModal;
window.saveSucursal = saveSucursal;
window.editSucursal = editSucursal;
window.deleteSucursal = deleteSucursal;
window.searchAddress = searchAddress;
