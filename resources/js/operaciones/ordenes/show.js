import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    initTaskForm();
    initPartForm();
    initOriginButtons();
});

function initTaskForm() {
    const form = document.getElementById('form-add-task');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const url = window.location.pathname + '/tasks';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: '¡Tarea Asignada!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                document.getElementById('modal-task').close();
                form.reset();
                window.location.reload(); // Re-render for simplicity or append row
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'No se pudo comunicar con el servidor', 'error');
        }
    });
}

function initPartForm() {
    const form = document.getElementById('form-add-part');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const url = window.location.pathname + '/details';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: '¡Repuesto Agregado!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                document.getElementById('modal-part').close();
                form.reset();
                window.location.reload();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'No se pudo comunicar con el servidor', 'error');
        }
    });
}

function initOriginButtons() {
    const buttons = document.querySelectorAll('.btn-origin');
    const input = document.querySelector('input[name="suministrado_por"]');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('btn-active', 'bg-opacity-20'));
            btn.classList.add('btn-active', 'bg-opacity-20');
            input.value = btn.dataset.value;
        });
    });
}
