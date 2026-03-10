import axios from 'axios';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    loadItems();

    const itemForm = document.getElementById('itemForm');
    itemForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        saveItem();
    });
});

window.loadItems = async () => {
    try {
        const res = await axios.get('/panel/mantenimientos/inventario-recepcion/list');
        const items = res.data;

        renderTable('documentos_accesorios', items.filter(i => i.seccion === 'documentos_accesorios'));
        renderTable('herramientas_exterior', items.filter(i => i.seccion === 'herramientas_exterior'));

    } catch (err) {
        console.error(err);
        Swal.fire('Error', 'No se pudieron cargar los ítems', 'error');
    }
};

function renderTable(seccion, items) {
    const tbody = document.querySelector(`#table-${seccion} tbody`);
    tbody.innerHTML = '';

    items.forEach(item => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-blue-50/50 transition-colors border-b border-gray-50';
        tr.innerHTML = `
            <td class="font-bold text-gray-700">${item.nombre}</td>
            <td><span class="badge badge-outline text-[10px] uppercase">${item.tipo.replace('_', ' ')}</span></td>
            <td class="text-center font-mono">${item.orden}</td>
            <td class="text-center">
                <span class="badge ${item.activo ? 'badge-success' : 'badge-error'} badge-sm text-white font-bold">
                    ${item.activo ? 'ACTIVO' : 'INACTIVO'}
                </span>
            </td>
            <td class="text-right">
                <div class="flex justify-end gap-1">
                    <button onclick="editItem(${JSON.stringify(item).replace(/"/g, '&quot;')})" class="btn btn-ghost btn-xs text-blue-600">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteItem(${item.id})" class="btn btn-ghost btn-xs text-red-600">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

window.openCreateModal = () => {
    const form = document.getElementById('itemForm');
    form.reset();
    document.getElementById('itemId').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Ítem de Inventario';
    document.getElementById('itemModal').showModal();
};

window.editItem = (item) => {
    document.getElementById('itemId').value = item.id;
    document.getElementById('nombre').value = item.nombre;
    document.getElementById('seccion').value = item.seccion;
    document.getElementById('tipo').value = item.tipo;
    document.getElementById('orden').value = item.orden;
    document.getElementById('activo').checked = item.activo;
    document.getElementById('modalTitle').innerText = 'Editar Ítem';
    document.getElementById('itemModal').showModal();
};

async function saveItem() {
    const id = document.getElementById('itemId').value;
    const data = {
        nombre: document.getElementById('nombre').value,
        seccion: document.getElementById('seccion').value,
        tipo: document.getElementById('tipo').value,
        orden: document.getElementById('orden').value,
        activo: document.getElementById('activo').checked,
    };

    try {
        let res;
        if (id) {
            res = await axios.put(`/panel/mantenimientos/inventario-recepcion/${id}`, data);
        } else {
            res = await axios.post('/panel/mantenimientos/inventario-recepcion', data);
        }

        if (res.data.success) {
            Swal.fire('Guardado', res.data.message, 'success');
            document.getElementById('itemModal').close();
            loadItems();
        }
    } catch (err) {
        console.error(err);
        const msg = err.response?.data?.message || 'Error al guardar';
        Swal.fire('Error', msg, 'error');
    }
}

window.deleteItem = async (id) => {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1e293b',
        cancelButtonColor: '#ef4444',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const res = await axios.delete(`/panel/mantenimientos/inventario-recepcion/${id}`);
            if (res.data.success) {
                Swal.fire('Eliminado', res.data.message, 'success');
                loadItems();
            }
        } catch (err) {
            Swal.fire('Error', 'No se pudo eliminar el ítem', 'error');
        }
    }
};
