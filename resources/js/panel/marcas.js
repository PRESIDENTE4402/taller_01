/**
 * Gestor de Marcas - JS Moderno y Funcional (Sin Alpine)
 */

document.addEventListener('DOMContentLoaded', () => {
    loadMarcas();

    // Filtro de búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#marcasGrid > div');

        cards.forEach(card => {
            const text = card.querySelector('h4').innerText.toLowerCase();
            card.style.display = text.includes(term) ? '' : 'none';
        });
    });
});

let isEditing = false;
let currentId = null;

// ==========================================
// CRUD Operations
// ==========================================

async function loadMarcas() {
    const gridContainer = document.getElementById('marcasGrid');
    const emptyState = document.getElementById('emptyState');

    // Skeleton loading can be implemented here if needed (e.g. inject skeleton cards)

    try {
        const response = await fetch(`${API_URL}/list`);
        const data = await response.json();

        gridContainer.innerHTML = ''; // Limpiar contenido previo

        if (data.length === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
            return;
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
        }

        data.forEach((marca) => {
            const card = document.createElement('div');
            // Hacer la tarjeta clickeable para ver modelos
            card.onclick = (e) => {
                // Evitar abrir modal si se clickea en botones de acción
                if (e.target.closest('button')) return;
                openModelosModal(marca.id, marca.nombre);
            };

            card.className = 'bg-slate-900 rounded-xl p-5 flex items-center justify-between group hover:shadow-lg hover:shadow-cyan-500/20 transition-all duration-300 border border-slate-800 cursor-pointer relative overflow-hidden';

            card.innerHTML = `
                <!-- Fondo con Gradiente Dinámico -->
                <div class="absolute inset-0 bg-gradient-to-br from-slate-800 to-slate-900 opacity-100 transition-all duration-500"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/0 via-cyan-500/5 to-blue-600/10 opacity-0 group-hover:opacity-100 transition-all duration-500"></div>

                <!-- Contenido -->
                <div class="relative z-10 flex items-center justify-between w-full h-full">
                    <div class="flex items-center gap-5">
                        <!-- Icono Marca -->
                        <div class="h-14 w-14 rounded-2xl bg-slate-800 shadow-inner flex items-center justify-center text-2xl font-black text-transparent bg-clip-text bg-gradient-to-br from-cyan-400 to-blue-600 border border-slate-700/50 group-hover:scale-110 transition-transform duration-300 shrink-0">
                            ${marca.nombre.charAt(0).toUpperCase()}
                        </div>
                        
                        <div class="flex flex-col justify-center">
                            <h4 class="text-white font-bold text-xl tracking-tight group-hover:text-cyan-400 transition-colors line-clamp-1">${marca.nombre}</h4>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700 group-hover:border-cyan-500/30 group-hover:text-cyan-400 transition-colors uppercase tracking-wider">
                                    <i class="fas fa-layer-group text-[9px] mr-1"></i> Ver Modelos
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex flex-col gap-2 opacity-100 sm:opacity-40 sm:group-hover:opacity-100 transition-all duration-300 translate-x-2 sm:translate-x-0 shrink-0">
                        <button onclick="editMarca(${marca.id}, '${marca.nombre}')" class="h-8 w-8 rounded-lg bg-slate-800 hover:bg-blue-600 hover:text-white text-slate-500 transition-all shadow-sm border border-slate-700 hover:border-blue-500 flex items-center justify-center p-0 z-20" title="Editar">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <button onclick="deleteMarca(${marca.id})" class="h-8 w-8 rounded-lg bg-slate-800 hover:bg-red-600 hover:text-white text-slate-500 transition-all shadow-sm border border-slate-700 hover:border-red-500 flex items-center justify-center p-0 z-20" title="Eliminar">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>
            `;
            gridContainer.appendChild(card);
        });

    } catch (error) {
        console.error('Error cargando marcas:', error);
        showToast('Error al cargar datos', 'error');
    }
}

async function saveMarca(e) {
    e.preventDefault();

    const nombreInput = document.getElementById('nombreMarca');
    const name = nombreInput.value;
    const errorSpan = document.getElementById('errorNombre');

    if (!name.trim()) {
        errorSpan.textContent = 'El nombre es obligatorio';
        errorSpan.classList.remove('hidden');
        return;
    }

    const method = isEditing ? 'PUT' : 'POST';
    const url = isEditing ? `${API_URL}/${currentId}` : API_URL;

    // Loading State
    const submitBtn = e.target.closest('.relative')?.querySelector('button[type="button"]'); // Hacky find or use ID
    // Better: use the form submit event handling

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json' // Importante para forzar respuesta JSON de Laravel
            },
            body: JSON.stringify({ nombre: name })
        });

        let result;
        try {
            result = await response.json();
        } catch (e) {
            console.error('Error parseando JSON:', e);
            throw new Error('Error del servidor (Respuesta no válida). Revisa la consola.');
        }

        if (!response.ok) {
            // Manejo de errores de validación (Laravel 422)
            if (response.status === 422 && result.errors && result.errors.nombre) {
                const errorMsg = result.errors.nombre[0];

                // Alerta Moderna de Duplicado
                Swal.fire({
                    title: '¡Atención!',
                    text: errorMsg,
                    icon: 'warning',
                    confirmButtonText: 'Entendido',
                    background: '#1e293b',
                    color: '#ffffff',
                    confirmButtonColor: '#3b82f6'
                });

                throw new Error(errorMsg);
            }
            throw new Error(result.message || 'Error en la petición');
        }

        // Success
        closeModal();
        loadMarcas();
        showToast(isEditing ? 'Marca actualizada' : 'Marca creada', 'success');

    } catch (error) {
        console.error(error);
        errorSpan.textContent = error.message;
        errorSpan.classList.remove('hidden');

        // Efecto de vibración/atención en el input
        nombreInput.classList.add('border-red-500', 'text-red-600');
        setTimeout(() => nombreInput.classList.remove('border-red-500', 'text-red-600'), 2000);
    }
}

async function deleteMarca(id) {
    const result = await Swal.fire({
        title: '¿Eliminar Marca?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-slate-700 rounded-xl'
        }
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Error al eliminar');

        loadMarcas();
        showToast('Marca eliminada', 'success');

    } catch (error) {
        showToast('No se pudo eliminar la marca', 'error');
    }
}

// ==========================================
// Modal Logic
// ==========================================

function openModal() {
    isEditing = false;
    currentId = null;
    document.getElementById('modalTitle').textContent = 'Nueva Marca';
    document.getElementById('nombreMarca').value = '';
    document.getElementById('errorNombre').classList.add('hidden');

    toggleModal(true);
}

function editMarca(id, nombre) {
    isEditing = true;
    currentId = id;
    document.getElementById('modalTitle').textContent = 'Editar Marca';
    document.getElementById('nombreMarca').value = nombre;
    document.getElementById('errorNombre').classList.add('hidden');

    toggleModal(true);
}

function closeModal() {
    toggleModal(false);
}

function toggleModal(show) {
    const modal = document.getElementById('marcaModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    if (show) {
        modal.classList.remove('hidden');
        // Small delay for transition
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }, 10);
    } else {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
}

// ==========================================
// Integración de Gestión de Modelos
// ==========================================

async function openModelosModal(marcaId, marcaNombre) {
    document.getElementById('currentMarcaId').value = marcaId;
    document.getElementById('marcaTitleName').textContent = marcaNombre;
    document.getElementById('nombreModelo').value = '';

    toggleModelosModal(true);
    await loadModelos(marcaId);
}

function closeModelosModal() {
    toggleModelosModal(false);
}

function toggleModelosModal(show) {
    const modal = document.getElementById('modelosModal');
    const backdrop = document.getElementById('modelosBackdrop');
    const panel = document.getElementById('modelosPanel');

    if (show) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'scale-95');
            panel.classList.add('opacity-100', 'scale-100');
        }, 10);
    } else {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'scale-100');
        panel.classList.add('opacity-0', 'scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
}

async function loadModelos(marcaId) {
    const listContainer = document.getElementById('listaModelos');
    const emptyState = document.getElementById('modelosEmpty');
    listContainer.innerHTML = '<div class="text-center py-4 text-slate-500"><i class="fas fa-circle-notch fa-spin"></i> Cargando...</div>';

    try {
        const response = await fetch(`${API_MODELOS_URL}/by-marca/${marcaId}`);
        const data = await response.json();

        listContainer.innerHTML = '';

        if (data.length === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            data.forEach(modelo => {
                const item = document.createElement('div');
                item.className = 'p-4 flex items-center justify-between hover:bg-white/5 transition-colors group';
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <i class="fas fa-angle-right text-cyan-500/50"></i>
                        <span class="text-slate-200 font-medium">${modelo.nombre}</span>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <button onclick="editModelo(${modelo.id}, '${modelo.nombre}')" class="text-blue-400 hover:text-blue-300 p-1" title="Editar">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <button onclick="deleteModelo(${modelo.id})" class="text-red-400 hover:text-red-300 p-1" title="Eliminar">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                `;
                listContainer.appendChild(item);
            });
        }
    } catch (error) {
        console.error(error);
        listContainer.innerHTML = '<div class="text-center py-4 text-red-400">Error al cargar modelos</div>';
    }
}

async function saveModelo(e) {
    e.preventDefault();
    const marcaId = document.getElementById('currentMarcaId').value;
    const nombreInput = document.getElementById('nombreModelo');
    const nombre = nombreInput.value.trim();

    if (!nombre) return;

    try {
        const response = await fetch(API_MODELOS_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ marca_id: marcaId, nombre: nombre })
        });

        const result = await response.json();

        if (!response.ok) {
            if (response.status === 422 && result.errors && result.errors.nombre) {
                Swal.fire({
                    title: '¡Atención!',
                    text: result.errors.nombre[0],
                    icon: 'warning',
                    background: '#1e293b',
                    color: '#ffffff',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            throw new Error(result.message || 'Error al guardar');
        }

        nombreInput.value = '';
        loadModelos(marcaId);
        showToast('Modelo agregado', 'success');

    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function deleteModelo(id) {
    const result = await Swal.fire({
        title: '¿Eliminar Modelo?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Sí, eliminar',
        background: '#0f172a',
        color: '#f8fafc'
    });

    if (!result.isConfirmed) return;

    try {
        await fetch(`${API_MODELOS_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        const marcaId = document.getElementById('currentMarcaId').value;
        loadModelos(marcaId);
        showToast('Modelo eliminado', 'success');
    } catch (error) {
        showToast('Error al eliminar modelo', 'error');
    }
}

// Editable Model (Simple Prompt wrapper for MVP)
async function editModelo(id, currentName) {
    const { value: newName } = await Swal.fire({
        title: 'Editar Modelo',
        input: 'text',
        inputValue: currentName,
        background: '#1e293b',
        color: '#ffffff',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        inputValidator: (value) => {
            if (!value) return 'El nombre es obligatorio';
        }
    });

    if (newName && newName !== currentName) {
        try {
            const response = await fetch(`${API_MODELOS_URL}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nombre: newName })
            });

            if (!response.ok) throw new Error('Error al actualizar');

            const marcaId = document.getElementById('currentMarcaId').value;
            loadModelos(marcaId);
            showToast('Modelo actualizado', 'success');

        } catch (error) {
            showToast('Error al actualizar modelo', 'error');
        }
    }
}

// Helpers globales para el HTML
window.loadMarcas = loadMarcas;
window.saveMarca = saveMarca;
window.deleteMarca = deleteMarca;
window.openModal = openModal;
window.editMarca = editMarca;
window.closeModal = closeModal;
// Nuevos Helpers Modelos
window.openModelosModal = openModelosModal;
window.closeModelosModal = closeModelosModal;
window.saveModelo = saveModelo;
window.deleteModelo = deleteModelo;
window.editModelo = editModelo;
