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

// Auxiliar para normalizar texto (quitar acentos)
function normalizeText(text) {
    return text.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

function applyFilters() {
    const searchInput = document.getElementById('searchInput');
    const term = searchInput ? normalizeText(searchInput.value) : '';
    const cards = document.querySelectorAll('#repuestosGrid > div');
    const emptyState = document.getElementById('emptyState');
    let visibleCount = 0;

    cards.forEach(card => {
        const nombre = normalizeText(card.querySelector('h4').innerText);
        const sku = card.querySelector('.text-slate-300') ? normalizeText(card.querySelector('.text-slate-300').innerText) : '';
        const marca = card.querySelector('p.text-slate-400\\/80') ? normalizeText(card.querySelector('p.text-slate-400\\/80').innerText) : '';
        
        // Buscar también en atributos visibles
        const atributos = Array.from(card.querySelectorAll('.flex.flex-wrap span'))
            .map(s => normalizeText(s.innerText))
            .join(' ');
        
        const cardCategoryId = card.dataset.category || '';
        
        const matchesSearch = nombre.includes(term) || sku.includes(term) || marca.includes(term) || atributos.includes(term);
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
            emptyState.querySelector('p').textContent = `No encontramos coincidencias para "${document.getElementById('searchInput').value}"`;
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
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mb-1.5">Stock</span>
                            <div class="flex items-center gap-2 bg-white px-2.5 py-1.5 rounded-lg shadow-sm border border-slate-100">
                                <span class="font-black text-sm leading-none ${item.stock_actual <= item.stock_minimo ? 'text-red-500' : 'text-slate-700'}">
                                    ${item.stock_actual}
                                </span>
                                <div class="w-2 h-2 rounded-full ${item.stock_actual <= item.stock_minimo ? 'bg-red-500 animate-pulse' : 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]'}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer de acciones dinámico -->
                <div class="bg-slate-50/50 backdrop-blur-md border-t border-slate-100 p-4 flex items-center justify-between gap-3 group-hover:bg-white transition-all duration-300">
                    
                    <!-- Botón Principal: Venta/Carrito -->
                    <button onclick='addToCart(${JSON.stringify(item)})' class="flex-1 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 bg-blue-600/10 text-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-500/30 active:scale-95 group/cart" title="Añadir a Venta">
                        <i class="fas fa-shopping-cart text-lg transition-transform group-hover/cart:scale-110"></i>
                    </button>

                    <!-- Botón Menú de Opciones -->
                    <div class="relative group/menu">
                        <button class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-300 bg-slate-200/50 text-slate-500 hover:bg-slate-900 hover:text-white active:scale-95">
                            <i class="fas fa-ellipsis-h text-lg"></i>
                        </button>

                        <!-- Menú Desplegable (Estilo Premium) -->
                        <div class="absolute bottom-full right-0 w-60 bg-white/95 backdrop-blur-xl rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.15)] border border-white p-3 opacity-0 translate-y-2 pointer-events-none group-hover/menu:opacity-100 group-hover/menu:translate-y-[-12px] group-hover/menu:pointer-events-auto transition-all duration-300 z-50
                                    before:content-[''] before:absolute before:top-full before:left-0 before:w-full before:h-4 before:bg-transparent">
                            
                            <div class="px-4 py-2 border-b border-slate-50 mb-2">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Gestión de Stock</p>
                            </div>
                            
                            <button onclick='openMovementModal(${JSON.stringify(item)}, "entrada")' class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-plus"></i></div>
                                <span>Ingresar Stock</span>
                            </button>

                            <button onclick='openMovementModal(${JSON.stringify(item)}, "salida")' class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-minus"></i></div>
                                <span>Egreso Manual</span>
                            </button>

                            <div class="h-[1px] bg-slate-100 my-1 mx-2"></div>

                            <button onclick="viewHistory(${item.id}, '${item.nombre.replace(/'/g, "\\'")}')" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center"><i class="fas fa-history"></i></div>
                                <span>Ver Historial</span>
                            </button>

                            <button onclick='editRepuesto(${JSON.stringify(item)})' class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-purple-50 hover:text-purple-600 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center"><i class="fas fa-pen-nib"></i></div>
                                <span>Editar Datos</span>
                            </button>

                            <div class="h-[1px] bg-slate-100 my-1 mx-2"></div>

                            <button onclick="deleteRepuesto(${item.id})" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-bold text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-trash-alt"></i></div>
                                <span>Eliminar</span>
                            </button>
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
            if (response.status === 422 && result.errors) {
                // Show first validation error if exists
                const firstError = Object.values(result.errors)[0][0];
                throw new Error(firstError);
            }
            throw new Error(result.message || result.error || 'Error al guardar');
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

            // Sincronizar conteos después de crear los elementos
            if (typeof window.updateSidebarCounts === 'function') window.updateSidebarCounts();
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
    
    // Auto-código visual reforzado
    const codigoInput = document.getElementById('codigoRepuesto');
    codigoInput.value = 'REP-XXXXX';
    codigoInput.readOnly = true;
    codigoInput.disabled = true;
    codigoInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-400', 'border-slate-200');
    codigoInput.closest('div').classList.add('opacity-70');
    
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
    const codigoInput = document.getElementById('codigoRepuesto');
    codigoInput.value = item.codigo_interno;
    codigoInput.readOnly = true;
    codigoInput.disabled = true;
    codigoInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-400', 'border-slate-200');
    codigoInput.closest('div').classList.add('opacity-70');

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

// ==========================================
// Inventario: Movimientos & Kardex
// ==========================================

async function openMovementModal(repuesto, tipo) {
    const isIngreso = tipo === 'entrada';
    const title = isIngreso ? 'Ingresar Stock' : 'Egreso / Venta de Stock';
    const icon = isIngreso ? 'fas fa-plus-circle text-emerald-500' : 'fas fa-minus-circle text-amber-500';
    
    const { value: formValues } = await Swal.fire({
        title: `<div class="flex items-center gap-3"><i class="${icon}"></i> <span>${title}</span></div>`,
        html: `
            <div class="text-left mt-4 px-2">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Producto: <span class="text-slate-900">${repuesto.nombre}</span></p>
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 ml-1">Cantidad</label>
                        <input type="number" id="swal-cantidad" step="0.01" class="swal2-input !m-0 w-full rounded-xl" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 ml-1">Motivo / Concepto</label>
                        <select id="swal-motivo" class="swal2-input !m-0 w-full rounded-xl text-sm">
                            ${isIngreso 
                                ? '<option value="compra">Compra / Abastecimiento</option><option value="ajuste">Ajuste de Inventario</option><option value="devolucion">Devolución de Cliente</option>'
                                : '<option value="ajuste">Ajuste / Pérdida</option><option value="consumo">Consumo Interno</option>'
                            }
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1 ml-1">Notas Adicionales</label>
                        <textarea id="swal-notas" class="swal2-textarea !m-0 w-full rounded-xl text-sm" placeholder="Ej. Factura #123..."></textarea>
                    </div>
                </div>
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: isIngreso ? 'Registrar Ingreso' : 'Registrar Salida',
        cancelButtonText: 'Cancelar',
        customClass: {
            title: 'text-xl font-black text-gray-800 border-b border-gray-100 pb-4',
            popup: 'rounded-3xl p-6 shadow-2xl',
            confirmButton: isIngreso 
                ? 'bg-emerald-600 text-white rounded-xl font-bold px-6 py-3 hover:bg-emerald-700 transition-all mr-2'
                : 'bg-amber-600 text-white rounded-xl font-bold px-6 py-3 hover:bg-amber-700 transition-all mr-2',
            cancelButton: 'bg-gray-100 text-gray-500 rounded-xl font-bold px-6 py-3 hover:bg-gray-200 transition-all'
        },
        buttonsStyling: false,
        preConfirm: () => {
            const cantidad = document.getElementById('swal-cantidad').value;
            const motivo = document.getElementById('swal-motivo').value;
            const notas = document.getElementById('swal-notas').value;
            
            if (!cantidad || cantidad <= 0) {
                Swal.showValidationMessage('Por favor ingresa una cantidad válida');
                return false;
            }
            return { cantidad, motivo, notas, tipo };
        }
    });

    if (formValues) {
        try {
            const response = await fetch(`${API_URL}/movement/${repuesto.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify(formValues)
            });

            const result = await response.json();
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Hecho!',
                    text: result.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                loadRepuestos(); // Recargar grid
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }
}

async function viewHistory(id, nombre) {
    Swal.fire({
        title: 'Cargando historial...',
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false
    });

    try {
        const response = await fetch(`${API_URL}/history/${id}`);
        const result = await response.json();

        if (result.success) {
            const movimientos = result.data;
            let timelineHtml = `
                <div class="text-left mt-2 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
                    <div class="relative pl-8 space-y-6 before:content-[''] before:absolute before:left-[11px] before:top-2 before:bottom-0 before:w-[2px] before:bg-slate-100">
            `;

            if (movimientos.length === 0) {
                timelineHtml += '<p class="text-slate-400 italic text-center py-10">No hay movimientos registrados aún.</p>';
            }

            movimientos.forEach(m => {
                const date = new Date(m.created_at).toLocaleString();
                const isIngreso = m.tipo === 'entrada';
                const colorClass = isIngreso ? 'bg-emerald-500 shadow-emerald-200' : 'bg-amber-500 shadow-amber-200';
                const icon = isIngreso ? 'fa-arrow-up' : 'fa-arrow-down';
                
                timelineHtml += `
                    <div class="relative">
                        <div class="absolute -left-[27px] top-1 w-5 h-5 rounded-full ${colorClass} shadow-lg border-4 border-white flex items-center justify-center z-10">
                            <i class="fas ${icon} text-[8px] text-white"></i>
                        </div>
                        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm hover:border-blue-100 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-[9px] font-black uppercase tracking-widest ${isIngreso ? 'text-emerald-600' : 'text-amber-600'} bg-${isIngreso ? 'emerald' : 'amber'}-50 px-2 py-0.5 rounded-md">${m.motivo.replace('_', ' ')}</span>
                                <span class="text-[9px] font-bold text-slate-300 uppercase">${date}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-700">${m.notas || 'Sin notas adicionales'}</span>
                                    <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1"><i class="fas fa-user-circle mr-1"></i> ${m.usuario.name}</span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-sm font-black ${isIngreso ? 'text-emerald-600' : 'text-amber-600'}">${isIngreso ? '+' : '-'}${parseFloat(m.cantidad)}</span>
                                    <span class="block text-[8px] font-bold text-slate-400 uppercase tracking-tighter">Stock: ${parseFloat(m.stock_nuevo)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            timelineHtml += '</div></div>';

            Swal.fire({
                title: `<div class="text-left"><p class="text-[10px] font-black text-blue-500 uppercase tracking-widest mb-1">Kardex / Historial</p><h3 class="text-lg font-black text-slate-800">${nombre}</h3></div>`,
                html: timelineHtml,
                width: '600px',
                showConfirmButton: true,
                confirmButtonText: 'Cerrar',
                customClass: {
                    title: 'border-b border-gray-100 pb-4',
                    popup: 'rounded-[2.5rem] p-8',
                    confirmButton: 'bg-slate-900 text-white rounded-xl font-bold px-8 py-3 hover:bg-black transition-all shadow-lg hover:shadow-blue-500/20'
                },
                buttonsStyling: false
            });
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudo cargar el historial', 'error');
    }
}

window.openMovementModal = openMovementModal;
window.viewHistory = viewHistory;

// ==========================================
// Carrito de Ventas (Multi-producto)
// ==========================================
let saleCart = [];

window.toggleCartDrawer = function() {
    const drawer = document.getElementById('cart-drawer');
    const backdrop = document.getElementById('cart-backdrop');
    const isOpen = !drawer.classList.contains('translate-x-full');

    if (isOpen) {
        drawer.classList.add('translate-x-full');
        backdrop.classList.add('hidden');
        backdrop.classList.remove('opacity-100');
    } else {
        drawer.classList.remove('translate-x-full');
        backdrop.classList.remove('hidden');
        setTimeout(() => backdrop.classList.add('opacity-100'), 10);
        renderCart();
    }
}

window.addToCart = function(item) {
    const existing = saleCart.find(i => i.id === item.id);
    if (existing) {
        if (existing.cantidad < item.stock_actual) {
            existing.cantidad++;
        } else {
            Swal.fire('Atención', 'No hay más stock disponible para este producto.', 'warning');
            return;
        }
    } else {
        saleCart.push({
            id: item.id,
            nombre: item.nombre,
            sku: item.codigo_interno,
            precio: parseFloat(item.precio_venta),
            stock: item.stock_actual,
            cantidad: 1
        });
    }

    updateCartBadge();
    
    // Feedback visual
    const floatBtn = document.getElementById('cart-float-btn');
    floatBtn.classList.remove('hidden');
    floatBtn.classList.add('animate-bounce');
    setTimeout(() => floatBtn.classList.remove('animate-bounce'), 1000);

    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-start',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: 'success',
        title: 'Producto añadido'
    });
}

function updateCartBadge() {
    const badge = document.getElementById('cart-badge');
    badge.textContent = saleCart.reduce((acc, current) => acc + current.cantidad, 0);
}

function renderCart() {
    const container = document.getElementById('cart-items-container');
    const totalEl = document.getElementById('cart-total');
    
    if (saleCart.length === 0) {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-center opacity-40">
                <i class="fas fa-cart-plus text-5xl text-slate-200 mb-4"></i>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">El carrito está vacío</p>
            </div>
        `;
        totalEl.textContent = '$0.00';
        document.getElementById('checkout-btn').disabled = true;
        return;
    }

    document.getElementById('checkout-btn').disabled = false;
    let html = '';
    let total = 0;

    saleCart.forEach((item, index) => {
        const itemTotal = item.precio * item.cantidad;
        total += itemTotal;
        html += `
            <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm hover:border-blue-100 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1">
                        <h4 class="text-sm font-black text-slate-800 line-clamp-1">${item.nombre}</h4>
                        <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">${item.sku}</p>
                    </div>
                    <button onclick="removeFromCart(${index})" class="text-slate-300 hover:text-red-500 transition-colors">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-100">
                        <button onclick="updateQty(${index}, -1)" class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-500 hover:text-blue-600 transition-all font-bold">-</button>
                        <span class="w-8 text-center text-xs font-black text-slate-700">${item.cantidad}</span>
                        <button onclick="updateQty(${index}, 1)" class="w-7 h-7 rounded-lg bg-white shadow-sm flex items-center justify-center text-slate-500 hover:text-blue-600 transition-all font-bold">+</button>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Subtotal</p>
                        <p class="text-sm font-black text-blue-600 leading-none">$${itemTotal.toFixed(2)}</p>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    totalEl.textContent = `$${total.toFixed(2)}`;
}

window.updateQty = function(index, delta) {
    const item = saleCart[index];
    const newQty = item.cantidad + delta;
    
    if (newQty <= 0) {
        removeFromCart(index);
    } else if (newQty > item.stock) {
        Swal.fire('Stock Limitado', `Solo quedan ${item.stock} unidades en stock.`, 'warning');
    } else {
        item.cantidad = newQty;
        renderCart();
        updateCartBadge();
    }
}

window.removeFromCart = function(index) {
    saleCart.splice(index, 1);
    renderCart();
    updateCartBadge();
    if (saleCart.length === 0) {
        document.getElementById('cart-float-btn').classList.add('hidden');
        toggleCartDrawer();
    }
}

window.toggleNewClientForm = function() {
    const searchContainer = document.getElementById('client-search-container');
    const newClientForm = document.getElementById('new-client-form');
    const toggleBtn = document.getElementById('toggle-new-client');
    const isNew = newClientForm.classList.contains('hidden');

    if (isNew) {
        newClientForm.classList.remove('hidden');
        searchContainer.classList.add('hidden');
        toggleBtn.textContent = '← Buscar Existente';
        toggleBtn.classList.replace('text-blue-600', 'text-slate-400');
        document.getElementById('selected-client-id').value = '';
        document.getElementById('cart-client-search').value = '';
    } else {
        newClientForm.classList.add('hidden');
        searchContainer.classList.remove('hidden');
        toggleBtn.textContent = '+ Nuevo Cliente';
        toggleBtn.classList.replace('text-slate-400', 'text-blue-600');
        // Clear new client inputs
        document.getElementById('new-client-name').value = '';
        document.getElementById('new-client-phone').value = '';
        document.getElementById('new-client-nit').value = '';
    }
}

window.searchClients = async function(query) {
    const resultsContainer = document.getElementById('client-results');
    if (!query || query.length < 2) {
        resultsContainer.classList.add('hidden');
        return;
    }

    try {
        const response = await fetch(`/panel/clientes/list?search=${query}`);
        const data = await response.json();
        const clients = data.data;

        if (clients.length === 0) {
            resultsContainer.innerHTML = '<div class="p-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">No se encontraron clientes</div>';
        } else {
            resultsContainer.innerHTML = clients.map(client => `
                <div onclick='selectClient(${JSON.stringify(client).replace(/'/g, "&apos;")})' class="p-4 hover:bg-blue-50 cursor-pointer transition-colors flex flex-col gap-1 group border-b border-slate-50 last:border-0">
                    <span class="text-xs font-black text-slate-700 group-hover:text-blue-600 transition-colors uppercase">${client.nombre_completo}</span>
                    <div class="flex items-center gap-3 text-[9px] font-bold text-slate-400 uppercase tracking-widest">
                        <span><i class="fas fa-phone-alt mr-1"></i> ${client.telefono || 'Sin tel'}</span>
                        <span><i class="fas fa-id-card mr-1"></i> NIT: ${client.nit || 'C/F'}</span>
                    </div>
                </div>
            `).join('');
        }
        resultsContainer.classList.remove('hidden');
    } catch (error) {
        console.error('Error searching clients:', error);
    }
}

window.selectClient = function(client) {
    document.getElementById('selected-client-id').value = client.id;
    document.getElementById('selected-client-name').textContent = client.nombre_completo;
    document.getElementById('selected-client-phone').textContent = client.telefono || 'Sin teléfono';
    document.getElementById('client-initial').textContent = client.nombre_completo.charAt(0).toUpperCase();

    document.getElementById('client-search-container').classList.add('hidden');
    document.getElementById('selected-client-badge').classList.remove('hidden');
    document.getElementById('client-results').classList.add('hidden');
    document.getElementById('toggle-new-client').classList.add('hidden');
}

window.deselectClient = function() {
    document.getElementById('selected-client-id').value = '';
    document.getElementById('client-search-container').classList.remove('hidden');
    document.getElementById('selected-client-badge').classList.add('hidden');
    document.getElementById('toggle-new-client').classList.remove('hidden');
    document.getElementById('cart-client-search').value = '';
    document.getElementById('cart-client-search').focus();
}

window.processCheckout = async function() {
    const btn = document.getElementById('checkout-btn');
    const notes = document.getElementById('cart-notes').value;
    
    let clienteId = document.getElementById('selected-client-id').value;
    let clienteNombre = 'Venta Mostrador';

    // Verificar si es cliente nuevo o existente
    const isNewClient = !document.getElementById('new-client-form').classList.contains('hidden');
    
    if (isNewClient) {
        const nombre = document.getElementById('new-client-name').value;
        const telefono = document.getElementById('new-client-phone').value;
        const nit = document.getElementById('new-client-nit').value;

        if (!nombre || !telefono) {
            Swal.fire('Atención', 'Nombre y Teléfono son obligatorios para un cliente nuevo.', 'warning');
            return;
        }

        // Crear cliente dinámicamente
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> REGISTRANDO CLIENTE...';
        
        try {
            const clientResponse = await fetch('/panel/clientes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nombre_completo: nombre, telefono, nit, situacion: 'activo' })
            });
            const clientRes = await clientResponse.json();
            if (clientRes.success) {
                clienteId = clientRes.data.id;
                clienteNombre = clientRes.data.nombre_completo;
            } else {
                throw new Error(clientRes.message);
            }
        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = '<span>FINALIZAR VENTA</span><i class="fas fa-check-circle ml-2"></i>';
            Swal.fire('Error', 'No se pudo registrar el cliente: ' + error.message, 'error');
            return;
        }
    } else if (clienteId) {
        clienteNombre = document.getElementById('selected-client-name').textContent;
    }

    const confirm = await Swal.fire({
        title: 'Confirmar Venta',
        text: `¿Deseas procesar la venta para ${clienteNombre}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, Finalizar',
        cancelButtonText: 'Revisar',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'bg-slate-900 text-white rounded-xl px-10 py-3 font-bold mr-2',
            cancelButton: 'bg-slate-100 text-slate-600 rounded-xl px-10 py-3 font-bold'
        }
    });

    if (confirm.isConfirmed) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> PROCESANDO VENTA...';

        const payload = {
            cliente_id: clienteId || null,
            cliente_nombre: clienteNombre,
            notas: notes,
            items: saleCart.map(i => ({
                id: i.id,
                cantidad: i.cantidad
            }))
        };

        try {
            const response = await fetch(`/panel/ventas`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'X-Sucursal-Id': document.querySelector('[name="sucursal_id"]')?.value || ''
                },
                body: JSON.stringify(payload)
            });

            const res = await response.json();

            if (res.success) {
                saleCart = [];
                updateCartBadge();
                deselectClient(); // Limpiar cliente
                document.getElementById('cart-notes').value = ''; // Limpiar notas
                document.getElementById('cart-float-btn').classList.add('hidden');
                toggleCartDrawer();
                if (window.loadRepuestos) loadRepuestos();
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Venta Realizada!',
                    text: `Folio generado: ${res.venta.folio}`,
                    customClass: {
                        popup: 'rounded-[2rem]',
                        confirmButton: 'bg-blue-600 text-white rounded-xl px-8 py-3 font-bold shadow-lg shadow-blue-500/30'
                    },
                    buttonsStyling: false
                });
            } else {
                throw new Error(res.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>FINALIZAR VENTA</span><i class="fas fa-check-circle ml-2"></i>';
        }
    }
}

window.toggleNewClientForm = toggleNewClientForm;
window.searchClients = searchClients;
window.selectClient = selectClient;
window.deselectClient = deselectClient;
window.processCheckout = processCheckout;
