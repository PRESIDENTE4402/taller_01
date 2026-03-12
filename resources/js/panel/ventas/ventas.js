document.addEventListener('DOMContentLoaded', function() {
    loadVentas();
});

let currentPage = 1;

async function loadVentas(page = 1) {
    currentPage = page;
    const container = document.getElementById('ventas-table-body');
    const dateStart = document.getElementById('filter-date-start').value;
    const dateEnd = document.getElementById('filter-date-end').value;
    
    try {
        let url = `${window.API_VENTAS_URL}/list?page=${page}`;
        if (dateStart) url += `&fecha_inicio=${dateStart}`;
        if (dateEnd) url += `&fecha_fin=${dateEnd}`;

        const response = await fetch(url);
        const data = await response.json();
        
        renderVentas(data.ventas.data);
        renderPagination(data.ventas);
        updateStats(data.total_filtrado, data.count_filtrado);
        
    } catch (error) {
        console.error('Error loading ventas:', error);
        container.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-red-500 font-bold">Error al cargar datos</td></tr>`;
    }
}

function setQuickFilter(type) {
    const startInput = document.getElementById('filter-date-start');
    const endInput = document.getElementById('filter-date-end');
    const today = new Date();
    
    // Función auxiliar para formatear fecha a YYYY-MM-DD (ISO) local
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (type === 'today') {
        const str = formatDate(today);
        startInput.value = str;
        endInput.value = str;
    } else if (type === 'yesterday') {
        const yesterday = new Date();
        yesterday.setDate(today.getDate() - 1);
        const str = formatDate(yesterday);
        startInput.value = str;
        endInput.value = str;
    } else if (type === 'month') {
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        startInput.value = formatDate(firstDay);
        endInput.value = formatDate(today);
    }

    loadVentas(1);
}

function clearFilters() {
    document.getElementById('filter-date-start').value = '';
    document.getElementById('filter-date-end').value = '';
    loadVentas(1);
}

window.clearFilters = clearFilters;
window.setQuickFilter = setQuickFilter;

function renderVentas(ventas) {
    const container = document.getElementById('ventas-table-body');
    
    if (!ventas || ventas.length === 0) {
        container.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-20 text-center opacity-40">
                    <i class="fas fa-receipt text-5xl text-slate-200 mb-4"></i>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">No hay ventas registradas</p>
                </td>
            </tr>
        `;
        return;
    }

    container.innerHTML = ventas.map(venta => {
        const date = new Date(venta.created_at);
        const dateStr = date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const timeStr = date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        
        let statusBadge = '';
        if (venta.estado === 'completada') {
            statusBadge = '<span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-emerald-100">Completada</span>';
        } else if (venta.estado === 'devuelta') {
            statusBadge = '<span class="px-3 py-1 bg-red-50 text-red-600 text-[10px] font-black rounded-full uppercase tracking-widest border border-red-100">Devuelta</span>';
        } else {
            statusBadge = `<span class="px-3 py-1 bg-slate-50 text-slate-500 text-[10px] font-black rounded-full uppercase tracking-widest border border-slate-100">${venta.estado}</span>`;
        }

        return `
            <tr class="hover:bg-slate-50/50 transition-colors group">
                <td class="px-6 py-4">
                    <span class="text-sm font-black text-slate-800">${venta.folio}</span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-slate-700">${venta.cliente_nombre || 'Venta Mostrador'}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">${dateStr} · ${timeStr}</span>
                    </div>
                </td>
                <td class="px-6 py-4">${statusBadge}</td>
                <td class="px-6 py-4 text-right">
                    <span class="text-sm font-black text-slate-900">$${parseFloat(venta.total).toFixed(2)}</span>
                </td>
                <td class="px-6 py-4 text-center">
                    <button onclick="viewVentaDetail(${venta.id})" class="w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:shadow-md transition-all active:scale-90">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function updateStats(total, count) {
    document.getElementById('stats-total').textContent = `$${parseFloat(total).toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
    document.getElementById('stats-count').textContent = count;
}

function renderPagination(data) {
    const container = document.getElementById('pagination-container');
    if (data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    container.innerHTML = `
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
            Página ${data.current_page} de ${data.last_page}
        </p>
        <div class="flex gap-2">
            <button onclick="loadVentas(${data.current_page - 1})" ${data.current_page === 1 ? 'disabled' : ''} class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button onclick="loadVentas(${data.current_page + 1})" ${data.current_page === data.last_page ? 'disabled' : ''} class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-50 disabled:opacity-30 disabled:cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;
}

async function viewVentaDetail(id) {
    try {
        const response = await fetch(`${window.API_VENTAS_URL}/${id}`);
        const venta = await response.json();
        
        showVentaModal(venta);
    } catch (error) {
        Swal.fire('Error', 'No se pudo cargar el detalle de la venta', 'error');
    }
}

function showVentaModal(venta) {
    const date = new Date(venta.created_at);
    document.getElementById('modal-folio').textContent = venta.folio;
    document.getElementById('modal-fecha').textContent = date.toLocaleString('es-ES');
    document.getElementById('modal-cliente').textContent = venta.cliente_nombre || 'Venta Mostrador';
    document.getElementById('modal-vendedor').textContent = venta.usuario ? venta.usuario.name : 'N/A';
    document.getElementById('modal-total').textContent = `$${parseFloat(venta.total).toFixed(2)}`;
    
    if (venta.notas) {
        document.getElementById('modal-notas-container').classList.remove('hidden');
        document.getElementById('modal-notas').textContent = venta.notas;
    } else {
        document.getElementById('modal-notas-container').classList.add('hidden');
    }

    const itemsBody = document.getElementById('modal-items-body');
    itemsBody.innerHTML = venta.detalles.map(d => `
        <tr class="hover:bg-slate-50/50">
            <td class="px-4 py-3">
                <div class="flex flex-col">
                    <span class="font-black text-slate-800">${d.repuesto ? d.repuesto.nombre : 'Producto Eliminado'}</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">${d.repuesto ? d.repuesto.codigo_interno : '-'}</span>
                </div>
            </td>
            <td class="px-4 py-3 text-center font-black text-slate-700">${d.cantidad}</td>
            <td class="px-4 py-3 text-right font-black text-blue-600">$${parseFloat(d.subtotal).toFixed(2)}</td>
        </tr>
    `).join('');

    const actionsContainer = document.getElementById('modal-actions');
    if (venta.estado === 'completada') {
        actionsContainer.innerHTML = `
            <button onclick="processReturn(${venta.id})" class="w-full bg-red-50 text-red-600 rounded-2xl py-4 font-black flex items-center justify-center gap-3 border border-red-100 hover:bg-red-600 hover:text-white transition-all active:scale-95 shadow-lg shadow-red-500/10">
                <i class="fas fa-undo"></i>
                <span>PROCESAR DEVOLUCIÓN TOTAL</span>
            </button>
        `;
    } else if (venta.estado === 'devuelta') {
        actionsContainer.innerHTML = `
            <div class="w-full bg-slate-100 text-slate-400 rounded-2xl py-4 font-black flex items-center justify-center gap-3 border border-slate-200 cursor-not-allowed">
                <i class="fas fa-check-double"></i>
                <span>VENTA YA DEVUELTA / REINTEGRADA</span>
            </div>
        `;
    } else {
        actionsContainer.innerHTML = '';
    }

    toggleVentaModal(true);
}

function toggleVentaModal(show) {
    const modal = document.getElementById('ventaModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    if (show) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4');
            panel.classList.add('opacity-100', 'translate-y-0');
        }, 10);
    } else {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'translate-y-0');
        panel.classList.add('opacity-0', 'translate-y-4');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
}

window.closeVentaModal = () => toggleVentaModal(false);

async function processReturn(id) {
    const { value: motivo } = await Swal.fire({
        title: 'Procesar Devolución',
        text: '¿Seguro que deseas devolver esta venta? Los productos regresarán al inventario.',
        input: 'textarea',
        inputPlaceholder: 'Escribe el motivo de la devolución...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, Reintegrar Stock',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'bg-red-600 text-white px-8 py-3 rounded-xl font-bold mr-2',
            cancelButton: 'bg-slate-100 text-slate-500 px-8 py-3 rounded-xl font-bold'
        }
    });

    if (motivo !== undefined) {
        try {
            const response = await fetch(`${window.API_VENTAS_URL}/${id}/return`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN
                },
                body: JSON.stringify({ motivo })
            });
            const res = await response.json();
            
            if (res.success) {
                Swal.fire('¡Éxito!', res.message, 'success');
                closeVentaModal();
                loadVentas(currentPage);
            } else {
                throw new Error(res.message);
            }
        } catch (error) {
            Swal.fire('Error', error.message, 'error');
        }
    }
}

window.viewVentaDetail = viewVentaDetail;
window.processReturn = processReturn;
window.loadVentas = loadVentas;
