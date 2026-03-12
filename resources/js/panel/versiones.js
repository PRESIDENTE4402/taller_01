/**
 * Gestor de Versiones - JS Moderno y Funcional
 * Arquitectura Jerárquica: Marcas -> Modelos -> Versiones
 */

// ==========================================
// Estado de Navegación
// ==========================================
let currentView = 'marcas'; // 'marcas' | 'modelos'
let selectedMarca = null; // { id, nombre }
let isEditing = false;
let currentId = null;

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar en vista de marcas
    loadMarcasView();

    // Filtro de búsqueda inteligente dependiente del contexto
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#versionesGrid > div');

        cards.forEach(card => {
            const textContent = card.innerText.toLowerCase();
            card.style.display = textContent.includes(term) ? '' : 'none';
        });
    });
});

// ==========================================
// Lógica de Vistas (Navegación)
// ==========================================

async function loadMarcasView() {
    currentView = 'marcas';
    selectedMarca = null;

    // UI Helpers
    document.getElementById('btnBack').classList.add('hidden');
    document.getElementById('viewTitle').innerHTML = 'Marcas';
    document.getElementById('viewSubtitle').textContent = 'Selecciona una marca para explorar sus modelos';
    const searchInput = document.getElementById('searchInput');
    searchInput.value = '';
    searchInput.placeholder = 'Buscar marca...';

    const grid = document.getElementById('versionesGrid');
    const emptyState = document.getElementById('emptyState');
    grid.innerHTML = '<div class="col-span-full py-12 flex justify-center"><i class="fas fa-circle-notch fa-spin text-cyan-500 text-3xl"></i></div>';
    emptyState.classList.add('hidden');

    try {
        const response = await fetch(API_MARCAS_URL);
        const marcas = await response.json();

        grid.innerHTML = '';
        if (marcas.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        }

        marcas.forEach(marca => {
            const card = document.createElement('div');
            card.onclick = () => loadModelosView(marca.id, marca.nombre);
            card.className = 'group relative bg-slate-900 rounded-2xl p-6 border border-slate-800 hover:border-cyan-500/50 transition-all duration-300 cursor-pointer overflow-hidden hover:shadow-lg hover:shadow-cyan-900/20';

            card.innerHTML = `
                <div class="absolute inset-0 bg-gradient-to-br from-slate-800 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10 flex items-center gap-5">
                    <div class="h-16 w-16 rounded-xl bg-slate-800 flex items-center justify-center text-3xl font-black text-transparent bg-clip-text bg-gradient-to-br from-cyan-400 to-blue-600 border border-slate-700 shadow-inner group-hover:scale-110 transition-transform duration-300">
                        ${marca.nombre.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-white group-hover:text-cyan-400 transition-colors">${marca.nombre}</h3>
                        <div class="flex items-center gap-2 mt-1">
                             <span class="text-xs font-bold text-slate-500 bg-slate-950 px-2 py-0.5 rounded border border-slate-800 group-hover:border-cyan-500/30 transition-colors">
                                EXPLORAR MODELOS
                             </span>
                        </div>
                    </div>
                    <div class="opacity-0 group-hover:opacity-100 transition-all transform translate-x-4 group-hover:translate-x-0">
                        <div class="h-8 w-8 rounded-full bg-cyan-500/10 flex items-center justify-center text-cyan-400">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </div>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });

    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div class="col-span-full text-center text-red-500">Error al cargar marcas</div>';
    }
}

async function loadModelosView(marcaId, marcaNombre) {
    currentView = 'modelos';
    selectedMarca = { id: marcaId, nombre: marcaNombre };

    // UI Helpers
    document.getElementById('btnBack').classList.remove('hidden');
    document.getElementById('viewTitle').innerHTML = `<span class="text-slate-500 opacity-50">${marcaNombre} /</span> Modelos`;
    document.getElementById('viewSubtitle').textContent = `Gestionando versiones para modelos de ${marcaNombre}`;
    const searchInput = document.getElementById('searchInput');
    searchInput.value = '';
    searchInput.placeholder = `Buscar modelo de ${marcaNombre}...`;

    const grid = document.getElementById('versionesGrid');
    const emptyState = document.getElementById('emptyState');
    grid.innerHTML = '<div class="col-span-full py-12 flex justify-center"><i class="fas fa-circle-notch fa-spin text-cyan-500 text-3xl"></i></div>';
    emptyState.classList.add('hidden');

    try {
        // Obtenemos todos los modelos (con count) y filtramos en cliente
        // Nota: Idealmente deberíamos tener un endpoint /marcas/{id}/modelos con conteo.
        const response = await fetch(`${API_URL}/list`);
        const allModelos = await response.json();

        // Filtrar por marca
        const modelosDeMarca = allModelos.filter(m => m.marca_id == marcaId);

        grid.innerHTML = '';
        if (modelosDeMarca.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center py-16 text-slate-500">
                    <div class="bg-slate-800 p-4 rounded-full mb-3">
                        <i class="fas fa-folder-open text-2xl opacity-50"></i>
                    </div>
                    <p class="font-medium">No hay modelos registrados para ${marcaNombre}.</p>
                    <p class="text-xs mt-1 opacity-70">Ve a la sección 'Modelos' para agregar nuevos.</p>
                </div>
           `;
            return;
        }

        modelosDeMarca.forEach(modelo => {
            const card = document.createElement('div');
            card.onclick = (e) => {
                if (e.target.closest('button')) return;
                openVersionesListModal(modelo.id, modelo.nombre, marcaNombre);
            };

            card.className = 'group relative bg-slate-900 rounded-xl p-5 border border-slate-800 hover:border-cyan-500/30 transition-all duration-300 cursor-pointer overflow-hidden hover:shadow-lg hover:shadow-cyan-500/10';

            card.innerHTML = `
                <!-- Hover Glow -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-cyan-500/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>

                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                             <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1 block">
                                MODELO
                            </span>
                            <h4 class="text-white font-bold text-xl tracking-tight leading-none group-hover:text-cyan-400 transition-colors">${modelo.nombre}</h4>
                        </div>
                        <div class="bg-slate-950 px-2.5 py-1 rounded w-fit border border-slate-800 flex items-center gap-2">
                             <div class="w-1.5 h-1.5 rounded-full ${modelo.versiones_count > 0 ? 'bg-cyan-500' : 'bg-slate-600'}"></div>
                             <span class="text-xs font-bold text-slate-300">${modelo.versiones_count} Versiones</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-4 border-t border-slate-800 border-dashed flex items-center justify-between text-slate-500 group-hover:text-slate-300 transition-colors">
                        <span class="text-xs font-medium flex items-center gap-1.5">
                            <i class="fas fa-list-ul text-cyan-500"></i> Gestionar Lista
                        </span>
                        <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform text-cyan-500 opacity-0 group-hover:opacity-100"></i>
                    </div>
                </div>
            `;
            grid.appendChild(card);
        });

    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div class="col-span-full text-center text-red-500">Error al cargar modelos</div>';
    }
}

function goBack() {
    loadMarcasView();
}


// ==========================================
// Modal Logic (Lista de Versiones)
// ==========================================

async function openVersionesListModal(modeloId, modeloNombre, marcaNombre) {
    document.getElementById('currentModeloIdList').value = modeloId;
    document.getElementById('versionesListTitle').innerHTML = `<span class="text-slate-400 font-normal mr-2">${marcaNombre}</span> ${modeloNombre}`;
    document.getElementById('quickNombreVersion').value = '';

    toggleVersionesListModal(true);
    await loadVersionesDeModelo(modeloId);
}

function closeVersionesListModal() {
    toggleVersionesListModal(false);
    // Recargar vista actual (modelos) para actualizar contadores
    if (selectedMarca) {
        loadModelosView(selectedMarca.id, selectedMarca.nombre);
    }
}

function toggleVersionesListModal(show) {
    const modal = document.getElementById('versionesListModal');
    const backdrop = document.getElementById('versionesListBackdrop');
    const panel = document.getElementById('versionesListPanel');

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

async function loadVersionesDeModelo(modeloId) {
    const listContainer = document.getElementById('listaVersionesContainer');
    const emptyState = document.getElementById('versionesListEmpty');
    listContainer.innerHTML = '<div class="text-center py-4 text-slate-500"><i class="fas fa-circle-notch fa-spin"></i> Cargando...</div>';

    try {
        const response = await fetch(`${API_URL}/by-modelo/${modeloId}`);
        const data = await response.json();

        listContainer.innerHTML = '';

        if (data.length === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');

            data.forEach(version => {
                const item = document.createElement('div');
                item.className = 'p-4 flex items-center justify-between hover:bg-white/5 transition-colors group border-b border-slate-700/50 last:border-0';
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-cyan-500/50 shadow-[0_0_8px_rgba(6,182,212,0.5)]"></div>
                        <span class="text-slate-200 font-bold text-sm tracking-wide">${version.nombre}</span>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                         <button onclick="editVersionQuick(${version.id}, '${version.nombre}', ${modeloId})" class="h-8 w-8 rounded bg-slate-700 hover:bg-blue-600 hover:text-white text-blue-400 transition-all flex items-center justify-center" title="Editar">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <button onclick="deleteVersion(${version.id}, ${modeloId})" class="h-8 w-8 rounded bg-slate-700 hover:bg-red-600 hover:text-white text-red-400 transition-all flex items-center justify-center" title="Eliminar">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                `;
                listContainer.appendChild(item);
            });
        }
    } catch (error) {
        console.error(error);
        listContainer.innerHTML = '<div class="text-center py-4 text-red-400">Error al cargar versiones</div>';
    }
}

// ==========================================
// CRUD Actions (Quick)
// ==========================================

async function saveQuickVersion(e) {
    e.preventDefault();
    const modeloId = document.getElementById('currentModeloIdList').value;
    const nombreInput = document.getElementById('quickNombreVersion');
    const nombre = nombreInput.value.trim();

    if (!nombre) return;

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ modelo_id: modeloId, nombre: nombre })
        });

        const result = await response.json();

        if (!response.ok) {
            if (response.status === 422 && result.errors && result.errors.nombre) {
                showToast(result.errors.nombre[0], 'warning');
                return;
            }
            throw new Error(result.message || 'Error al guardar');
        }

        nombreInput.value = '';
        loadVersionesDeModelo(modeloId);
        showToast('Versión agregada', 'success');

    } catch (error) {
        showToast(error.message, 'error');
    }
}

async function editVersionQuick(id, currentName, modeloId) {
    const { value: newName } = await Swal.fire({
        title: 'Editar Versión',
        input: 'text',
        inputValue: currentName,
        background: '#1e293b',
        color: '#ffffff',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        customClass: {
            input: 'bg-slate-800 border-slate-600 text-white'
        },
        inputValidator: (value) => {
            if (!value) return 'El nombre es obligatorio';
        }
    });

    if (newName && newName !== currentName) {
        try {
            const response = await fetch(`${API_URL}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nombre: newName, modelo_id: modeloId })
            });

            if (!response.ok) throw new Error('Error al actualizar');

            loadVersionesDeModelo(modeloId);
            showToast('Versión actualizada', 'success');

        } catch (error) {
            showToast('Error al actualizar versión', 'error');
        }
    }
}

async function deleteVersion(id, modeloId) {
    const result = await Swal.fire({
        title: '¿Eliminar Versión?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-slate-700 rounded-xl',
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg mr-2',
            cancelButton: 'bg-slate-600 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-lg'
        },
        buttonsStyling: false
    });

    if (!result.isConfirmed) return;

    try {
        await fetch(`${API_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            }
        });

        loadVersionesDeModelo(modeloId);
        showToast('Versión eliminada', 'success');
    } catch (error) {
        showToast('Error al eliminar versión', 'error');
    }
}

// ==========================================
// Helpers Globales
// ==========================================

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-red-600' : (type === 'warning' ? 'bg-amber-500' : 'bg-blue-600'));

    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-xl shadow-lg shadow-black/30 z-50 flex items-center gap-3 transform transition-all duration-500 translate-y-20 opacity-0 font-bold tracking-wide`;
    toast.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check' : (type === 'error' ? 'fa-times' : 'fa-info')}"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-20', 'opacity-0');
    });

    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Expose functions for onclick events
window.goBack = goBack;
window.saveQuickVersion = saveQuickVersion;
window.editVersionQuick = editVersionQuick;
window.deleteVersion = deleteVersion;
window.openVersionesListModal = openVersionesListModal;
window.closeVersionesListModal = closeVersionesListModal;
