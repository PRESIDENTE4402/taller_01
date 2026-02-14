/**
 * Gestor de Repuestos (SaaS)
 */

document.addEventListener('DOMContentLoaded', () => {
    loadRepuestos();
    loadCategoriasDropdown();

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#repuestosGrid > div');
        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            card.style.display = text.includes(term) ? '' : 'none';
        });
    });
});

let isEditing = false;
let currentId = null;

// ==========================================
// CRUD Operations
// ==========================================

async function loadRepuestos() {
    const gridContainer = document.getElementById('repuestosGrid');
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

        data.forEach(item => {
            const attributesHtml = renderAttributesBadge(item.atributos);
            
            const card = document.createElement('div');
            card.className = 'bg-white rounded-xl shadow-sm border border-slate-100 hover:shadow-lg transition-all group relative overflow-hidden flex flex-col';
            
            card.innerHTML = `
                <div class="absolute top-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-10 bg-white/90 backdrop-blur rounded-bl-xl">
                    <button onclick='editRepuesto(${JSON.stringify(item)})' class="text-blue-500 hover:bg-blue-50 p-1.5 rounded-lg transition-colors">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button onclick="deleteRepuesto(${item.id})" class="text-red-500 hover:bg-red-50 p-1.5 rounded-lg transition-colors">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <div class="p-5 flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase tracking-wider">${item.categoria ? item.categoria.nombre : 'Sin Cat.'}</span>
                        <span class="text-xs font-mono text-slate-400">${item.codigo_interno}</span>
                    </div>
                    
                    <h4 class="font-bold text-gray-800 text-lg mb-1 leading-tight">${item.nombre}</h4>
                    <p class="text-sm text-gray-500 mb-3">${item.marca_repuesto || 'Genérico'}</p>

                    <div class="flex flex-wrap gap-1 mb-4">
                        ${attributesHtml}
                    </div>
                </div>

                <div class="bg-slate-50 p-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="block text-xs text-slate-400">Precio</span>
                        <span class="font-bold text-slate-800 text-lg">$${parseFloat(item.precio_venta).toFixed(2)}</span>
                    </div>
                    <div class="text-right">
                        <span class="block text-xs text-slate-400">Stock</span>
                        <span class="font-bold ${item.stock_actual <= item.stock_minimo ? 'text-red-500' : 'text-green-600'}">
                            ${item.stock_actual} Unid.
                        </span>
                    </div>
                </div>
            `;
            gridContainer.appendChild(card);
        });

    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'No se pudieron cargar los repuestos', 'error');
    }
}

function renderAttributesBadge(atributos) {
    if (!atributos || Object.keys(atributos).length === 0) return '';
    
    let html = '';
    let count = 0;
    for (const [key, value] of Object.entries(atributos)) {
        if (count > 2) {
            html += `<span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] rounded-full border border-slate-200">+${Object.keys(atributos).length - 3}</span>`;
            break;
        }
        html += `<span class="px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] rounded-full border border-blue-100" title="${key}: ${value}">${value}</span>`;
        count++;
    }
    return html;
}

// ==========================================
// Dynamic Attributes Logic
// ==========================================

function addAttributeRow(key = '', value = '') {
    const container = document.getElementById('attributesContainer');
    const rowId = 'attr_' + Date.now();
    
    const row = document.createElement('div');
    row.className = 'flex gap-2 items-center animate-fade-in-up';
    row.id = rowId;
    
    row.innerHTML = `
        <input type="text" class="attr-key w-1/3 text-xs rounded border-gray-200 focus:border-blue-500 focus:ring-blue-500 bg-white" 
            placeholder="Clave (Ej. Voltaje)" value="${key}">
        <input type="text" class="attr-value w-1/2 text-xs rounded border-gray-200 focus:border-blue-500 focus:ring-blue-500 bg-white" 
            placeholder="Valor (Ej. 12V)" value="${value}">
        <button type="button" onclick="document.getElementById('${rowId}').remove()" 
            class="text-red-400 hover:text-red-600 p-1 transition-colors">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    container.appendChild(row);
}

function collectAttributes() {
    const container = document.getElementById('attributesContainer');
    const rows = container.querySelectorAll('div'); // Direct children rows
    const attributes = {};
    
    rows.forEach(row => {
        const keyInput = row.querySelector('.attr-key');
        const valueInput = row.querySelector('.attr-value');
        if (keyInput && valueInput && keyInput.value.trim() !== '') {
            attributes[keyInput.value.trim()] = valueInput.value.trim();
        }
    });
    
    return attributes;
}

async function saveRepuesto(e) {
    e.preventDefault();

    const payload = {
        nombre: document.getElementById('nombreRepuesto').value,
        codigo_interno: document.getElementById('codigoRepuesto').value,
        marca_repuesto: document.getElementById('marcaRepuesto').value,
        categoria_id: document.getElementById('categoriaRepuesto').value || null,
        precio_costo: document.getElementById('precioCosto').value,
        precio_venta: document.getElementById('precioVenta').value,
        stock_actual: document.getElementById('stockActual').value,
        stock_minimo: document.getElementById('stockMinimo').value,
        sucursal_id: document.querySelector('meta[name="user-sucursal-id"]')?.content || 1,
        atributos: collectAttributes()
    };
    
    const url = isEditing ? `${API_URL}/${currentId}` : API_URL;
    const method = isEditing ? 'PUT' : 'POST';

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!response.ok) {
            if (response.status === 422) {
                // Show first validation error
                const firstError = Object.values(result.errors)[0][0];
                throw new Error(firstError);
            }
            throw new Error(result.message || 'Error al guardar');
        }

        closeModal();
        loadRepuestos();
        Swal.fire({
            icon: 'success',
            title: isEditing ? 'Actualizado' : 'Creado',
            showConfirmButton: false,
            timer: 1500
        });

    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    }
}

async function deleteRepuesto(id) {
    const result = await Swal.fire({
        title: '¿Eliminar?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar'
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

        if (!response.ok) throw new Error('Error');
        
        loadRepuestos();
        Swal.fire('Eliminado', 'El repuesto ha sido eliminado.', 'success');

    } catch (error) {
        Swal.fire('Error', 'No se pudo eliminar', 'error');
    }
}

// ==========================================
// Helpers & Modal
// ==========================================

async function loadCategoriasDropdown() {
    try {
        const response = await fetch(`${API_CATEGORIAS_URL}/list`);
        const data = await response.json();
        const select = document.getElementById('categoriaRepuesto');
        
        select.innerHTML = '<option value="">Selecciona una categoría</option>';
        data.forEach(cat => {
            select.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
        });
    } catch (e) {
        console.error('Error loading dropdown', e);
    }
}

function openModal() {
    isEditing = false;
    currentId = null;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-box text-blue-500"></i><span>Nuevo Repuesto</span>';
    document.getElementById('repuestoForm').reset();
    document.getElementById('attributesContainer').innerHTML = ''; // Clear attributes
    toggleModal(true);
}

function editRepuesto(item) {
    // Note: 'item' passed directly might behave weirdly with quotes in HTML. 
    // In production, fetch details by ID is safer.
    // For now, if simple strings, it works.
    
    isEditing = true;
    currentId = item.id;
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen text-purple-500"></i><span>Editar Repuesto</span>';
    
    document.getElementById('nombreRepuesto').value = item.nombre;
    document.getElementById('codigoRepuesto').value = item.codigo_interno;
    document.getElementById('marcaRepuesto').value = item.marca_repuesto || '';
    document.getElementById('categoriaRepuesto').value = item.categoria_id || '';
    document.getElementById('precioCosto').value = item.precio_costo;
    document.getElementById('precioVenta').value = item.precio_venta;
    document.getElementById('stockActual').value = item.stock_actual;
    document.getElementById('stockMinimo').value = item.stock_minimo;
    
    // Populate JSON attributes
    const container = document.getElementById('attributesContainer');
    container.innerHTML = '';
    if (item.atributos) {
        Object.entries(item.atributos).forEach(([key, value]) => {
            addAttributeRow(key, value);
        });
    }

    toggleModal(true);
}

function closeModal() {
    toggleModal(false);
}

function toggleModal(show) {
    const modal = document.getElementById('repuestoModal');
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
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
}

window.saveRepuesto = saveRepuesto;
window.deleteRepuesto = deleteRepuesto;
window.editRepuesto = editRepuesto; // Careful with object passing in HTML
window.openModal = openModal;
window.closeModal = closeModal;
window.addAttributeRow = addAttributeRow;
