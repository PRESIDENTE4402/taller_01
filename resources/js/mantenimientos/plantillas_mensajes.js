document.addEventListener('DOMContentLoaded', () => {
    loadTemplates();
    initForm();
    initCanalWatcher();
});

async function loadTemplates() {
    try {
        const response = await fetch('/panel/mantenimientos/plantillas-mensajes/list');
        const templates = await response.json();

        // Clear tables
        document.getElementById('tbody-Citas').innerHTML = '';
        document.getElementById('tbody-Órdenes').innerHTML = '';

        templates.forEach(t => {
            const tableBody = document.getElementById(`tbody-${t.categoria}`);
            if (tableBody) {
                const row = document.createElement('tr');
                row.className = 'hover:bg-blue-50/50 transition-colors group';

                const canalBadge = getCanalBadge(t.tipo_canal);
                const previewText = t.cuerpo.length > 60 ? t.cuerpo.substring(0, 60) + '...' : t.cuerpo;

                row.innerHTML = `
                    <td class="font-bold text-gray-700 py-4">
                        <div class="flex flex-col">
                            <span>${t.nombre}</span>
                            <span class="text-[9px] text-gray-400 font-mono italic">slug: ${t.slug}</span>
                        </div>
                    </td>
                    <td>${canalBadge}</td>
                    <td class="text-[11px] text-gray-500 font-medium italic break-words max-w-xs">${previewText}</td>
                    <td class="text-center">
                        <span class="badge ${t.activo ? 'badge-success' : 'badge-ghost'} badge-sm font-black uppercase text-[9px] italic border-none px-3">
                            ${t.activo ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td class="text-right flex justify-end gap-2 py-4">
                        <button onclick="editTemplate(${JSON.stringify(t).replace(/"/g, '&quot;')})" class="btn btn-ghost btn-xs text-blue-600 hover:bg-blue-100 rounded-lg">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);
            }
        });
    } catch (error) {
        console.error('Error al cargar plantillas:', error);
    }
}

function getCanalBadge(canal) {
    if (canal === 'whatsapp') return '<span class="badge bg-green-100 text-green-700 border-none font-black text-[9px] uppercase italic"><i class="fab fa-whatsapp mr-1"></i> WhatsApp</span>';
    if (canal === 'email') return '<span class="badge bg-blue-100 text-blue-700 border-none font-black text-[9px] uppercase italic"><i class="fas fa-envelope mr-1"></i> Email</span>';
    return '<span class="badge bg-indigo-100 text-indigo-700 border-none font-black text-[9px] uppercase italic"><i class="fas fa-layer-group mr-1"></i> Ambos</span>';
}

function initForm() {
    const form = document.getElementById('templateForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('templateId').value;
        const method = id ? 'PUT' : 'POST';
        const url = id ? `/panel/mantenimientos/plantillas-mensajes/${id}` : '/panel/mantenimientos/plantillas-mensajes';

        const data = {
            nombre: document.getElementById('nombre').value,
            categoria: document.getElementById('categoria').value,
            tipo_canal: document.getElementById('tipo_canal').value,
            cuerpo: document.getElementById('cuerpo').value,
            asunto: document.getElementById('asunto').value,
            activo: document.getElementById('activo').checked ? 1 : 0
        };

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.success) {
                Swal.fire({
                    title: '¡Guardado!',
                    text: result.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl p-6 shadow-2xl' }
                });
                document.getElementById('templateModal').close();
                loadTemplates();
            } else {
                Swal.fire('Error', result.message, 'error');
            }
        } catch (error) {
            console.error('Error al guardar:', error);
            Swal.fire('Error', 'No se pudo procesar la solicitud', 'error');
        }
    });
}

function initCanalWatcher() {
    const select = document.getElementById('tipo_canal');
    const asuntoContainer = document.getElementById('asunto-container');

    select.addEventListener('change', () => {
        if (select.value === 'whatsapp') {
            asuntoContainer.classList.add('hidden');
        } else {
            asuntoContainer.classList.remove('hidden');
        }
    });
}

window.openCreateModal = function () {
    document.getElementById('templateForm').reset();
    document.getElementById('templateId').value = '';
    document.getElementById('modalTitle').textContent = 'Nueva Plantilla';
    document.getElementById('asunto-container').classList.remove('hidden');
    document.getElementById('templateModal').showModal();
}

window.editTemplate = function (t) {
    document.getElementById('templateId').value = t.id;
    document.getElementById('nombre').value = t.nombre;
    document.getElementById('categoria').value = t.categoria;
    document.getElementById('tipo_canal').value = t.tipo_canal;
    document.getElementById('cuerpo').value = t.cuerpo;
    document.getElementById('asunto').value = t.asunto || '';
    document.getElementById('activo').checked = !!t.activo;

    document.getElementById('modalTitle').textContent = 'Editar Plantilla';

    if (t.tipo_canal === 'whatsapp') {
        document.getElementById('asunto-container').classList.add('hidden');
    } else {
        document.getElementById('asunto-container').classList.remove('hidden');
    }

    document.getElementById('templateModal').showModal();
}
