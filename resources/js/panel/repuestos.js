/**
 * Gestor de Repuestos (SaaS)
 */

document.addEventListener('DOMContentLoaded', () => {
    loadRepuestos();
    loadCategoriasDropdown();

    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', () => {
        applyFilters();
    });
});

window.currentCategoryFilter = 'all';
window.repuestosCategoryCounts = {};
let isEditing = false;
let currentId = null;

window.updateSidebarCounts = function() {
    if (!window.repuestosCategoryCounts) return;
    
    // Todos count
    const allCount = document.getElementById('count-cat-all');
    if (allCount) {
        allCount.textContent = window.repuestosCategoryCounts['all'] || 0;
    }

    // Individual category counts
    document.querySelectorAll('.cat-count:not(#count-cat-all)').forEach(badge => {
        const id = badge.dataset.countId;
        const count = window.repuestosCategoryCounts[id] || 0;
        badge.textContent = count;
        
        const isSelected = badge.closest('button').dataset.categoryId == window.currentCategoryFilter;
        if (count > 0) {
            badge.className = isSelected 
                ? 'text-[10px] font-black bg-white/30 text-white px-2 py-0.5 rounded-full cat-count transition-colors'
                : 'text-[10px] font-black bg-blue-100/70 text-blue-600 px-2 py-0.5 rounded-full cat-count transition-colors';
        } else {
            badge.className = isSelected
                ? 'text-[10px] font-black bg-white/20 text-white/50 px-2 py-0.5 rounded-full cat-count transition-colors'
                : 'text-[10px] font-black bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full cat-count transition-colors';
        }
    });
};

// Funciones de filtrado global
function applyFilters() {
    const term = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase() : '';
    const cards = document.querySelectorAll('#repuestosGrid > div');
    const emptyState = document.getElementById('emptyState');
    let visibleCount = 0;

    cards.forEach(card => {
        const text = card.innerText.toLowerCase();
        const cardCategoryId = card.dataset.category || '';
        
        const matchesSearch = text.includes(term);
        const matchesCategory = window.currentCategoryFilter === 'all' || cardCategoryId === window.currentCategoryFilter.toString();

        if (matchesSearch && matchesCategory) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    if (emptyState) {
        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
            emptyState.querySelector('h3').textContent = 'No hay resultados';
            emptyState.querySelector('p').textContent = 'Intenta con otros filtros o términos de búsqueda.';
        } else {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
            emptyState.querySelector('h3').textContent = 'Sin repuestos';
            emptyState.querySelector('p').textContent = 'Registra tu inventario de productos.';
        }
    }
}

window.filterByCategory = function(categoryId) {
    window.currentCategoryFilter = categoryId;
    
    // UI Actualización visual de los botones de la barra lateral
    document.querySelectorAll('.cat-filter').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white', 'shadow-lg', 'shadow-blue-600/30');
        btn.classList.add('bg-transparent', 'text-slate-600', 'hover:bg-slate-50', 'hover:text-blue-600');
    });
    
    const activeBtn = categoryId === 'all' 
        ? document.getElementById('btn-cat-all') 
        : document.querySelector(`.cat-filter[data-category-id="${categoryId}"]`);
        
    if (activeBtn) {
        activeBtn.classList.remove('bg-transparent', 'text-slate-600', 'hover:bg-slate-50', 'hover:text-blue-600');
        activeBtn.classList.add('bg-blue-600', 'text-white', 'shadow-lg', 'shadow-blue-600/30');
    }

    applyFilters();
    if (typeof window.updateSidebarCounts === 'function') window.updateSidebarCounts();
};

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

        window.repuestosCategoryCounts = { 'all': data.length };

        data.forEach(item => {
            const attributesHtml = renderAttributesBadge(item.atributos);
            const catId = item.categoria_id || 'null';
            window.repuestosCategoryCounts[catId] = (window.repuestosCategoryCounts[catId] || 0) + 1;
            
            const card = document.createElement('div');
            card.className = 'bg-gradient-to-br from-white to-slate-50/50 rounded-[1.25rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-white hover:border-blue-100 hover:shadow-[0_20px_40px_rgb(59,130,246,0.1)] transition-all duration-500 group relative flex flex-col overflow-hidden hover:-translate-y-1.5';
            card.dataset.category = item.categoria_id || '';
            
            // Atributos muy sutiles si existen
            const attrBox = attributesHtml ? `<div class="flex flex-wrap gap-1 mt-4 mb-2">${attributesHtml.replace(/bg-blue-50 text-blue-600 border border-blue-100/g, 'bg-white text-slate-500 border border-slate-200 shadow-sm')}</div>` : '';

            card.innerHTML = `
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/0 via-blue-500/0 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                
                <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 flex gap-2 z-10 bg-white/90 backdrop-blur-md rounded-lg p-1.5 shadow-lg shadow-black/5 border border-white">
                    <button onclick='editRepuesto(${JSON.stringify(item)})' class="text-blue-500 hover:text-white hover:bg-blue-500 w-7 h-7 rounded-md flex items-center justify-center transition-all bg-transparent">
                        <i class="fas fa-pen text-[11px]"></i>
                    </button>
                    <button onclick="deleteRepuesto(${item.id})" class="text-red-500 hover:text-white hover:bg-red-500 w-7 h-7 rounded-md flex items-center justify-center transition-all bg-transparent">
                        <i class="fas fa-trash text-[11px]"></i>
                    </button>
                </div>

                <div class="p-6 flex-1 flex flex-col relative z-0">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-[10px] font-black text-blue-600 bg-blue-50/50 px-2 py-1 rounded-md border border-blue-100/50 uppercase tracking-widest leading-none drop-shadow-sm">${item.categoria ? item.categoria.nombre : 'GENERAL'}</span>
                        <span class="text-[9px] font-bold text-slate-300 group-hover:text-slate-500 transition-colors uppercase tracking-widest">${item.codigo_interno}</span>
                    </div>
                    
                    <h4 class="font-black text-slate-800 text-[1.1rem] mb-1.5 leading-tight group-hover:text-blue-600 transition-colors line-clamp-2 drop-shadow-sm">${item.nombre}</h4>
                    <p class="text-[10px] text-slate-400/80 font-bold uppercase tracking-widest mb-auto">${item.marca_repuesto || 'GENÉRICA'}</p>

                    ${attrBox}

                    <div class="flex justify-between items-end mt-6 border-t border-slate-200/50 pt-5 relative">
                        <div class="absolute -top-[1px] left-0 w-12 h-[1px] bg-gradient-to-r from-blue-500 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Precio Unit.</span>
                            <span class="font-black text-slate-900 text-2xl leading-none drop-shadow-sm group-hover:text-blue-600 transition-colors"><span class="text-sm font-bold text-slate-400 mr-0.5 group-hover:text-blue-400/50">$</span>${parseFloat(item.precio_venta).toFixed(2)}</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Disponibilidad</span>
                            <div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-lg shadow-sm border border-slate-100">
                                <span class="font-black text-sm leading-none ${item.stock_actual <= item.stock_minimo ? 'text-red-500' : 'text-slate-700'}">
                                    ${item.stock_actual}
                                </span>
                                <div class="w-2 h-2 rounded-full ${item.stock_actual <= item.stock_minimo ? 'bg-red-500 animate-pulse' : 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]'}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            gridContainer.appendChild(card);
        });

        if (typeof window.updateSidebarCounts === 'function') window.updateSidebarCounts();


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
        confirmButtonText: 'Sí, eliminar',
        background: '#0f172a',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-slate-700 rounded-xl',
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg mr-2',
            cancelButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg'
        },
        buttonsStyling: false
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
        
        // 1. Dropdown form
        const select = document.getElementById('categoriaRepuesto');
        select.innerHTML = '<option value="">Selecciona una categoría</option>';
        data.forEach(cat => {
            select.innerHTML += `<option value="${cat.id}">${cat.nombre}</option>`;
        });

        // 2. Sidebar Filters
        const sidebar = document.getElementById('categoriasSidebarFilters');
        if (sidebar) {
            // Keep the 'Todas' button and clear the rest
            const allBtn = sidebar.querySelector('#btn-cat-all');
            sidebar.innerHTML = '';
            if (allBtn) sidebar.appendChild(allBtn);

            data.forEach(cat => {
                const btn = document.createElement('button');
                btn.className = 'cat-filter group px-5 py-3.5 rounded-2xl text-sm font-bold bg-transparent text-slate-600 hover:bg-slate-50 hover:text-blue-600 transition-all hover:-translate-y-0.5 active:scale-95 flex items-center justify-between w-full';
                btn.dataset.categoryId = cat.id;
                btn.onclick = () => filterByCategory(cat.id);
                btn.innerHTML = `
                    <span class="relative z-10 truncate drop-shadow-sm">${cat.nombre}</span>
                    <div class="flex items-center gap-2 relative z-10">
                        <span class="text-[10px] font-black bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full cat-count transition-colors" data-count-id="${cat.id}">0</span>
                        <i class="fas fa-chevron-right text-[10px] opacity-0 group-hover:opacity-70 group-hover:translate-x-1 transition-all"></i>
                    </div>
                `;
                sidebar.appendChild(btn);
            });
        }
    } catch (e) {
        console.error('Error loading dropdown and sidebar filters', e);
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
