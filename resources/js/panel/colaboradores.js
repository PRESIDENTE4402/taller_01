document.addEventListener('DOMContentLoaded', () => {
    loadBoard();

    const sucursalFilter = document.getElementById('sucursalFilter');
    if (sucursalFilter) {
        sucursalFilter.addEventListener('change', loadBoard);
    }

    const form = document.getElementById('formAssignTask');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(window.routes.assign, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    Swal.fire('¡Éxito!', result.message, 'success');
                    document.getElementById('modalAssignTask').close();
                    form.reset();
                    loadBoard();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo asignar la tarea', 'error');
            }
        });
    }
});

async function loadBoard() {
    const activeTasksTable = document.getElementById('activeTasksTable');
    const workersTable = document.getElementById('workersTable');
    const activeTasksCount = document.getElementById('activeTasksCount');
    const sucursalId = (document.getElementById('sucursalFilter')?.value || '').trim();

    try {
        const response = await fetch(`${window.routes.list}?sucursal_id=${encodeURIComponent(sucursalId)}`);
        const collaborators = await response.json();

        // 1. Render Active Tasks Table
        const activities = collaborators.filter(c => c.tarea_actual && c.tarea_actual.estado === 'en_progreso');
        if (activities.length > 0) {
            activeTasksTable.innerHTML = activities.map(c => `
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-50">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-black text-xs uppercase underline decoration-2 underline-offset-4">${c.nombre.charAt(0)}</div>
                            <span class="font-bold text-gray-800 text-sm italic underline decoration-blue-500/30">${c.nombre}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 font-bold text-gray-700 text-xs italic">
                        <i class="fas fa-quote-left mr-2 text-blue-200"></i>${c.tarea_actual.descripcion}
                    </td>
                    <td class="py-4 px-6 text-center">
                        ${c.tarea_actual.orden ? `
                            <div class="flex flex-col items-center">
                                <span class="badge badge-outline border-blue-400 text-blue-600 font-black text-[9px] uppercase tracking-tighter h-5">OT-${c.tarea_actual.orden.codigo_orden}</span>
                                <span class="text-[8px] font-black text-gray-400 uppercase mt-1 tracking-widest">${c.tarea_actual.orden.vehiculo?.placa || ''}</span>
                            </div>
                        ` : '<span class="badge badge-ghost text-gray-400 text-[8px] font-black uppercase tracking-widest italic border-gray-100 px-3">Tarea General</span>'}
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="badge badge-info border-none font-black italic uppercase tracking-tighter text-[9px]">EN PROGRESO</span>
                    </td>
                    <td class="py-4 px-6 text-right font-mono text-[10px] text-gray-400 font-bold">
                        ${new Date(c.tarea_actual.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </td>
                </tr>
            `).join('');
            activeTasksCount.textContent = `${activities.length} ACTIVAS`;
        } else {
            activeTasksTable.innerHTML = `<tr><td colspan="5" class="py-10 text-center text-gray-400 uppercase text-[10px] font-black tracking-widest italic">No hay actividades en curso</td></tr>`;
            activeTasksCount.textContent = `0 ACTIVAS`;
        }

        // 2. Render Workers Table
        workersTable.innerHTML = collaborators.map(c => {
            const statusMeta = {
                'disponible': { badge: 'badge-success', label: 'Disponible' },
                'ocupado': { badge: 'badge-info', label: 'Ocupado' },
                'pausado': { badge: 'badge-warning', label: 'En Pausa' }
            };
            const meta = statusMeta[c.estado_laboral] || statusMeta['disponible'];

            return `
                <tr class="hover:bg-gray-50 transition-colors border-b border-gray-50 group cursor-pointer" onclick="window.location.href='/panel/colaboradores/${c.id}'">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl ${c.estado_laboral === 'disponible' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600'} flex items-center justify-center font-black text-sm uppercase">${c.nombre.charAt(0)}</div>
                            <div class="flex flex-col">
                                <span class="font-black text-gray-800 text-sm group-hover:text-blue-600 transition-colors">${c.nombre}</span>
                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Mecánico</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center font-bold text-gray-500 text-xs uppercase tracking-tighter">${c.sucursal}</td>
                    <td class="py-4 px-6 text-center">
                        <div class="inline-flex flex-col items-center">
                            <span class="text-sm font-black text-gray-800 leading-none">${c.tareas_completadas_hoy}</span>
                            <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Trabajos</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="badge ${meta.badge} border-none font-black italic uppercase tracking-tighter text-[9px] px-3">${meta.label}</span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="event.stopPropagation(); openAssignModal(${c.id}, '${c.nombre}')" class="btn btn-xs btn-primary rounded-lg font-black italic uppercase text-[9px] shadow-md shadow-blue-500/20">
                                <i class="fas fa-plus"></i> Asignar
                            </button>
                            <a href="/panel/colaboradores/${c.id}" class="btn btn-xs btn-outline border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-800 rounded-lg">
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

    } catch (error) {
        console.error(error);
        activeTasksTable.innerHTML = '<tr><td colspan="5" class="text-center text-red-500 font-bold p-10">Error al cargar datos</td></tr>';
        workersTable.innerHTML = '<tr><td colspan="5" class="text-center text-red-500 font-bold p-10">Error al cargar datos</td></tr>';
    }
}

window.openAssignModal = (id, name) => {
    document.getElementById('inputUserId').value = id;
    document.getElementById('targetWorkerName').innerText = name.toUpperCase();
    document.getElementById('modalAssignTask').showModal();
}

window.loadBoard = loadBoard;
