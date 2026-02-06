/**
 * Gestor de Versiones - JS Moderno y Funcional
 */

document.addEventListener('DOMContentLoaded', () => {
    loadVersiones();
    loadModelos(); // Pre-cargar modelos para el modal

    // Filtro de búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#versionesGrid > div');

        cards.forEach(card => {
            const versionText = card.querySelector('h4').innerText.toLowerCase();
            const modelText = card.querySelector('.model-tag').innerText.toLowerCase();
            const fullText = versionText + ' ' + modelText;

            card.style.display = fullText.includes(term) ? '' : 'none';
        });
    });
});

let isEditing = false;
let currentId = null;

// ==========================================
// CRUD Operations
// ==========================================

async function loadVersiones() {
    const gridContainer = document.getElementById('versionesGrid');
    const emptyState = document.getElementById('emptyState');

    try {
        const response = await fetch(`${API_URL}/list`);
        const data = await response.json();

        gridContainer.innerHTML = '';

        if (data.length === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
            return;
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
        }

        data.forEach((version) => {
            const card = document.createElement('div');
            // Estilo Sólido Dark (Igual al de Marcas)
            card.className = 'bg-slate-900 rounded-xl p-5 flex items-center justify-between group hover:shadow-lg hover:shadow-cyan-500/20 transition-all duration-300 border border-slate-800';

            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-lg bg-slate-800 flex items-center justify-center text-cyan-500 font-bold border border-slate-700">
                        ${version.nombre.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <div class="text-xs text-slate-400 font-mono mb-1 model-tag">${version.marca_nombre} ${version.modelo_nombre}</div>
                        <h4 class="text-white font-bold text-lg leading-tight tracking-wide">${version.nombre}</h4>
                    </div>
                </div>
                
                <div class="flex gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300">
                    <button onclick="editVersion(${version.id}, '${version.nombre}', ${version.modelo_id})" class="h-9 w-9 rounded-lg bg-slate-800 text-blue-400 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center border border-slate-700" title="Editar">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button onclick="deleteVersion(${version.id})" class="h-9 w-9 rounded-lg bg-slate-800 text-red-400 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center border border-slate-700" title="Eliminar">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </div>
            `;
            gridContainer.appendChild(card);
        });

    } catch (error) {
        console.error('Error cargando versiones:', error);
        showToast('Error al cargar datos', 'error');
    }
}

async function loadModelos() {
    try {
        const response = await fetch(`${API_URL}/modelos-list`);
        const modelos = await response.json();
        const select = document.getElementById('modeloSelect');

        // Mantener la primera opción placeholder
        select.innerHTML = '<option value="">Seleccione un modelo...</option>';

        modelos.forEach(modelo => {
            const option = document.createElement('option');
            option.value = modelo.id;
            option.textContent = `${modelo.marca_nombre} - ${modelo.nombre}`;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error cargando modelos:', error);
    }
}

async function saveVersion(e) {
    e.preventDefault();

    const nombreInput = document.getElementById('nombreVersion');
    const modeloSelect = document.getElementById('modeloSelect');
    const name = nombreInput.value;
    const modeloId = modeloSelect.value;
    const errorSpan = document.getElementById('errorNombre');

    if (!modeloId) {
        showToast('Debe seleccionar un modelo', 'error');
        return;
    }

    if (!name.trim()) {
        errorSpan.textContent = 'El nombre es obligatorio';
        errorSpan.classList.remove('hidden');
        return;
    }

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
            body: JSON.stringify({ nombre: name, modelo_id: modeloId })
        });

        let result;
        try {
            result = await response.json();
        } catch (e) {
            console.error('Error parseando JSON:', e);
            throw new Error('Error del servidor (Respuesta no válida). Revisa la consola.');
        }

        if (!response.ok) {
            if (response.status === 422 && result.errors) {
                // Check specific errors
                let errorMsg = 'Error de validación';
                if (result.errors.nombre) errorMsg = result.errors.nombre[0];
                else if (result.errors.modelo_id) errorMsg = result.errors.modelo_id[0];

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

        closeModal();
        loadVersiones();
        showToast(isEditing ? 'Versión actualizada' : 'Versión creada', 'success');

    } catch (error) {
        console.error(error);
        if (!response.ok && response.status !== 422) { // Only show toast if logic specific handling missed it
            showToast(error.message, 'error');
        } else if (response.status === 422) {
            // Already handled by sweet alert
        } else {
            errorSpan.textContent = error.message;
            errorSpan.classList.remove('hidden');
        }
    }
}

async function deleteVersion(id) {
    const result = await Swal.fire({
        title: '¿Eliminar Versión?',
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

        loadVersiones();
        showToast('Versión eliminada', 'success');

    } catch (error) {
        showToast('No se pudo eliminar la versión', 'error');
    }
}

// ==========================================
// Modal Logic
// ==========================================

function openModal() {
    isEditing = false;
    currentId = null;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-tag text-cyan-500"></i><span>Registro de Versión</span>';
    document.getElementById('nombreVersion').value = '';
    document.getElementById('modeloSelect').value = '';
    document.getElementById('errorNombre').classList.add('hidden');

    toggleModal(true);
}

function editVersion(id, nombre, modeloId) {
    isEditing = true;
    currentId = id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit text-cyan-500"></i><span>Editar Versión</span>';
    document.getElementById('nombreVersion').value = nombre;
    document.getElementById('modeloSelect').value = modeloId;
    document.getElementById('errorNombre').classList.add('hidden');

    toggleModal(true);
}

function closeModal() {
    toggleModal(false);
}

function toggleModal(show) {
    const modal = document.getElementById('versionModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    if (show) {
        modal.classList.remove('hidden');
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
// UI Helpers
// ==========================================

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600');

    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 transform transition-all duration-300 translate-y-20 opacity-0`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')}"></i>
        <span class="font-medium text-sm">${message}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-y-20', 'opacity-0');
    }, 10);

    // Remove after 3s
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Hacer funciones accesibles globalmente para el HTML
window.loadVersiones = loadVersiones;
window.saveVersion = saveVersion;
window.deleteVersion = deleteVersion;
window.openModal = openModal;
window.editVersion = editVersion;
window.closeModal = closeModal;
