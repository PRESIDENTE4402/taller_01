document.addEventListener('DOMContentLoaded', function () {
    loadPermissions();
});

let isEditing = false;
let currentId = null;

const modal = document.getElementById('permissionModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalPanel = document.getElementById('modalPanel');
const modalTitle = document.getElementById('modalTitle');
const nameInput = document.getElementById('permissionName');
const descInput = document.getElementById('permissionDesc');
const errorName = document.getElementById('errorName');

async function loadPermissions() {
    const grid = document.getElementById('permissionsGrid');
    const empty = document.getElementById('emptyState');

    try {
        const response = await fetch(`${API_URL}/list`);
        const data = await response.json();

        grid.innerHTML = '';

        if (data.length === 0) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            return;
        }
        empty.classList.add('hidden');
        empty.classList.remove('flex');

        data.forEach(permiso => {
            const card = document.createElement('div');
            card.className = 'bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center hover:shadow-md transition-shadow';
            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">${permiso.nombre}</h4>
                        <p class="text-[10px] text-gray-400 font-mono">${permiso.slug}</p>
                        <p class="text-xs text-gray-500 mt-1 max-w-xs line-clamp-2">${permiso.descripcion || 'Sin descripción'}</p>
                    </div>
                </div>
                <div class="flex gap-1">
                    <button onclick="editPermission(${permiso.id}, '${permiso.nombre}', '${(permiso.descripcion || '').replace(/'/g, "\\'")}')" class="text-gray-400 hover:text-emerald-600 p-2"><i class="fas fa-pen"></i></button>
                    <button onclick="deletePermission(${permiso.id})" class="text-gray-400 hover:text-red-600 p-2"><i class="fas fa-trash"></i></button>
                </div>
            `;
            grid.appendChild(card);
        });
    } catch (e) {
        console.error(e);
    }
}

function openModal(edit = false) {
    modal.classList.remove('hidden');
    setTimeout(() => {
        modalBackdrop.classList.remove('opacity-0');
        modalPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        modalPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
    }, 10);

    if (!edit) {
        isEditing = false;
        document.getElementById('permissionForm').reset();
        modalTitle.innerHTML = '<i class="fas fa-plus-circle text-emerald-500"></i> Nuevo Permiso';
    }
}

function closeModal() {
    modalBackdrop.classList.add('opacity-0');
    modalPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
    modalPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function editPermission(id, nombre, descripcion) {
    isEditing = true;
    currentId = id;
    nameInput.value = nombre;
    descInput.value = descripcion || '';
    modalTitle.innerHTML = '<i class="fas fa-pen-nib text-emerald-500"></i> Editar Permiso';
    openModal(true);
}

async function savePermission(e) {
    e.preventDefault();
    errorName.classList.add('hidden');

    const nombre = nameInput.value;
    const descripcion = descInput.value;
    if (!nombre.trim()) return;

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
            body: JSON.stringify({ nombre, descripcion })
        });

        const result = await response.json();

        if (!response.ok) {
            if (result.errors?.nombre) {
                errorName.textContent = result.errors.nombre[0];
                errorName.classList.remove('hidden');
            }
            return;
        }

        closeModal();
        loadPermissions();
        Swal.fire({
            icon: 'success',
            title: isEditing ? 'Permiso Actualizado' : 'Permiso Creado',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            background: '#ffffff',
            color: '#1e293b'
        });

    } catch (error) {
        console.error(error);
    }
}

function deletePermission(id) {
    Swal.fire({
        title: '¿Eliminar Permiso?',
        text: "Esta acción revocará este permiso de todos los roles asignados.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'No, cancelar',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (response.ok) {
                    loadPermissions();
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'El permiso ha sido eliminado.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
}

window.openModal = openModal;
window.closeModal = closeModal;
window.savePermission = savePermission;
window.editPermission = editPermission;
window.deletePermission = deletePermission;
