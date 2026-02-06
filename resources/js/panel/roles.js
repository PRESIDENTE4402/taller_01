document.addEventListener('DOMContentLoaded', function () {
    loadRoles();
});

let isEditing = false;
let currentId = null;

const modal = document.getElementById('roleModal');
const modalBackdrop = document.getElementById('modalBackdrop');
const modalPanel = document.getElementById('modalPanel');
const modalTitle = document.getElementById('modalTitle');
const nameInput = document.getElementById('roleName');
const descInput = document.getElementById('roleDesc');
const errorName = document.getElementById('errorName');

async function loadRoles() {
    const grid = document.getElementById('rolesGrid');
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

        data.forEach(role => {
            const card = document.createElement('div');
            card.className = 'bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center hover:shadow-md transition-shadow';
            card.innerHTML = `
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800">${role.nombre}</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-xs line-clamp-2">${role.descripcion || 'Sin descripción'}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="editRole(${role.id}, '${role.nombre}', '${(role.descripcion || '').replace(/'/g, "\\'")}')" class="text-gray-400 hover:text-blue-600 p-2"><i class="fas fa-pen"></i></button>
                    <button onclick="deleteRole(${role.id})" class="text-gray-400 hover:text-red-600 p-2"><i class="fas fa-trash"></i></button>
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
        document.getElementById('roleForm').reset();
        modalTitle.textContent = 'Nuevo Rol';
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

function editRole(id, nombre, descripcion) {
    isEditing = true;
    currentId = id;
    nameInput.value = nombre;
    descInput.value = descripcion || '';
    modalTitle.textContent = 'Editar Rol';
    openModal(true);
}

async function saveRole(e) {
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
        loadRoles();
        Swal.fire({
            icon: 'success',
            title: isEditing ? 'Actualizado' : 'Creado',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });

    } catch (error) {
        console.error(error);
    }
}

function deleteRole(id) {
    Swal.fire({
        title: '¿Eliminar Rol?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-error text-white ml-2 rounded-lg',
            cancelButton: 'btn btn-ghost text-gray-600 rounded-lg'
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch(`${API_URL}/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (response.ok) {
                    loadRoles();
                    Swal.fire('Eliminado', 'El rol ha sido eliminado.', 'success');
                }
            } catch (e) {
                console.error(e);
            }
        }
    });
}

window.openModal = openModal;
window.closeModal = closeModal;
window.saveRole = saveRole;
window.editRole = editRole;
window.deleteRole = deleteRole;
