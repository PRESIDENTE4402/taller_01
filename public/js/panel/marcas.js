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
            card.className = 'bg-slate-900 rounded-xl p-5 flex items-center justify-between group hover:shadow-lg hover:shadow-cyan-500/20 transition-all duration-300 border border-slate-800';

            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-lg bg-slate-800 flex items-center justify-center text-cyan-500 font-bold border border-slate-700">
                        ${marca.nombre.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-lg leading-tight tracking-wide">${marca.nombre}</h4>
                    </div>
                </div>
                
                <div class="flex gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300">
                    <button onclick="editMarca(${marca.id}, '${marca.nombre}')" class="h-9 w-9 rounded-lg bg-slate-800 text-blue-400 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center border border-slate-700" title="Editar">
                        <i class="fas fa-pen text-xs"></i>
                    </button>
                    <button onclick="deleteMarca(${marca.id})" class="h-9 w-9 rounded-lg bg-slate-800 text-red-400 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center border border-slate-700" title="Eliminar">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
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
                'X-CSRF-TOKEN': CSRF_TOKEN
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
// UI Helpers
// ==========================================

function showToast(message, type = 'info') {
    // Simple Toast implementation
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-blue-600');

    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-3 transform transition-all duration-300 translate-y-20 opacity-0`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle')}"></i>
        <span class="font-medium text-sm">${message}</span>
    `;

    document.body.appendChild(toast);

    // Animate In
    setTimeout(() => {
        toast.classList.remove('translate-y-20', 'opacity-0');
    }, 10);

    // Remove after 3s
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
