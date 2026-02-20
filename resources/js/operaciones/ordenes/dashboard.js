import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {
    // Delegación de eventos para botones de cancelar
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cancelar');
        if (btn) {
            const id = btn.dataset.id;
            const cliente = btn.dataset.cliente;
            const url = btn.dataset.url;
            cancelarCita(id, cliente, url);
        }
    });
});

/**
 * Procesa la cancelación de una cita (inasistencia)
 * @param {number|string} id 
 * @param {string} cliente 
 * @param {string} url 
 */
function cancelarCita(id, cliente, url) {
    Swal.fire({
        title: '¿El cliente no asistió?',
        text: `Se marcará la cita de ${cliente} como "No asistió" y desaparecerá del listado de recepción.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, marcar inasistencia',
        cancelButtonText: 'Volver',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-error px-6 shadow-md mx-2',
            cancelButton: 'btn btn-ghost mx-2 text-gray-500'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Actualizando...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Listo!',
                            text: 'La cita ha sido marcada correctamente.',
                            icon: 'success',
                            timer: 1000,
                            showConfirmButton: false
                        });

                        // --- Lógica Dinámica Sin Recargar ---
                        const card = document.getElementById(`cita-card-${id}`);
                        if (card) {
                            card.classList.add('opacity-0', 'scale-95');
                            card.style.transition = 'all 0.3s ease';

                            setTimeout(() => {
                                card.remove();
                                updateDynamicCounters(-1);

                                const remaining = document.querySelectorAll('.appointment-card').length;
                                if (remaining === 0) {
                                    const noCitasMsg = document.getElementById('no-citas-msg');
                                    const citasContainer = document.getElementById('citas-container');
                                    if (noCitasMsg) noCitasMsg.classList.remove('hidden');
                                    if (citasContainer) citasContainer.classList.add('hidden');
                                }
                            }, 300);
                        }
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo actualizar la cita', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error de Conexión', 'Ocurrió un problema al comunicarse con el servidor', 'error');
                });
        }
    });
}

/**
 * Actualiza los contadores del dashboard tras una acción
 * @param {number} diff 
 */
function updateDynamicCounters(diff) {
    const resultsCount = document.getElementById('results-count');
    const statsCount = document.getElementById('stats-pendientes-count');

    if (resultsCount) {
        let currentString = resultsCount.innerText;
        let current = parseInt(currentString);
        resultsCount.innerText = `${current + diff} Resultados`;
    }

    if (statsCount) {
        let current = parseInt(statsCount.innerText);
        statsCount.innerText = current + diff;
    }
}
