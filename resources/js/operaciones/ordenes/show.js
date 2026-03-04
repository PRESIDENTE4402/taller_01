document.addEventListener('DOMContentLoaded', () => {
    initTaskForm();
    initPartForm();
    initOriginButtons();
    initStatusActions();
    initDetailStatusUpdates();
    initNotesButtons();
});

function initStatusActions() {
    const btnFinalizar = document.querySelector('.btn-finalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', async () => {
            const { isConfirmed } = await Swal.fire({
                title: '¿Finalizar Orden?',
                text: 'Se marcará como terminada y se registrará la fecha de hoy.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar'
            });

            if (isConfirmed) {
                updateStatus('finalizada');
            }
        });
    }
}

async function updateStatus(nuevoEstado) {
    const url = window.location.pathname + '/status';
    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ estado: nuevoEstado })
        });

        const data = await response.json();
        if (data.success) {
            Swal.fire('¡Éxito!', data.message, 'success').then(() => window.location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
    }
}

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

function initDetailStatusUpdates() {
    const selects = document.querySelectorAll('.status-detail-select');
    selects.forEach(select => {
        select.addEventListener('change', async (e) => {
            const tr = e.target.closest('tr');
            const detailId = tr.dataset.detailId;
            const nuevoEstado = e.target.value;
            const url = window.location.pathname + '/details/' + detailId + '/status';

            try {
                const response = await fetch(url, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ estado: nuevoEstado })
                });

                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
            }
        });
    });
}

window.enviarCotizacion = function (telefono, linkPdf, codigoOrden) {
    Swal.fire({
        title: 'Enviar Cotización',
        html: `
            <p class="text-sm text-gray-400 font-bold mb-4 uppercase tracking-widest text-center">¿Cómo deseas enviar esta cotización?</p>
            <div class="flex flex-col gap-3 px-2">
                <button id="btn-whatsapp" class="btn bg-[#25D366] hover:bg-[#1ebe59] border-none text-white w-full gap-3 font-black shadow-lg shadow-[#25D366]/30 text-lg flex items-center justify-center">
                    <i class="fab fa-whatsapp text-2xl"></i> Enviar por WhatsApp
                </button>
                <div class="divider text-gray-300 text-xs font-black uppercase tracking-widest my-1">O alternativas</div>
                <button id="btn-email" class="btn bg-blue-600 hover:bg-blue-700 border-none text-white w-full gap-3 font-bold shadow-lg shadow-blue-600/30 flex items-center justify-center">
                    <i class="fas fa-envelope text-xl"></i> Enviar PDF por Correo
                </button>
            </div>
        `,
        icon: 'info',
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cerrar ventana',
        customClass: {
            cancelButton: 'btn btn-ghost border border-gray-200 hover:bg-gray-100 text-gray-600 w-full mt-4 font-bold',
            popup: 'rounded-2xl shadow-xl border border-gray-100 bg-white p-6'
        },
        buttonsStyling: false,
        didOpen: () => {
            const btnWa = document.getElementById('btn-whatsapp');
            const btnEmail = document.getElementById('btn-email');

            btnWa.addEventListener('click', () => {
                let numeroLimpio = telefono.replace(/\D/g, '');

                // Formatear si no tiene codigo de area GT (502).
                if (numeroLimpio.length === 8) {
                    numeroLimpio = '502' + numeroLimpio;
                }

                const mensaje = encodeURIComponent(
                    `*Taller Mecánico - Cotización Orden #${codigoOrden}*\n\n` +
                    `Hola! Te enviamos una actualización/cotización sobre los servicios de tu vehículo.\n\n` +
                    `📄 *Puedes ver y descargar el detalle completo aquí:*\n${linkPdf}\n\n` +
                    `¡Quedamos a la espera de tu confirmación para proceder!`
                );

                window.open(`https://wa.me/${numeroLimpio}?text=${mensaje}`, '_blank');
                Swal.close();
            });

            btnEmail.addEventListener('click', () => {
                Swal.fire({
                    title: 'Enviando...',
                    text: 'Conectando con el servidor de correo...',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-2xl border border-gray-100 p-6 shadow-xl'
                    }
                }).then(() => {
                    Swal.fire({
                        title: 'Enviado',
                        text: 'La cotización PDF fue enviada al correo registrado del cliente.',
                        icon: 'success',
                        customClass: {
                            confirmButton: 'btn bg-blue-600 hover:bg-blue-700 text-white border-none rounded-xl'
                        },
                        buttonsStyling: false
                    });
                });
            });
        }
    });
}

window.guardarDiagnostico = async function () {
    const diag = document.getElementById('val-diagnostico').value;
    const diagFinal = document.getElementById('val-diagnostico-final').value;
    const url = window.location.pathname + '/diagnostico';

    try {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                diagnostico: diag,
                diagnostico_final: diagFinal
            })
        });

        const data = await response.json();
        if (data.success) {
            Swal.fire({
                title: 'Diagnóstico Guardado',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch (error) {
        console.error(error);
        Swal.fire('Error', 'No se pudo guardar el diagnóstico', 'error');
    }
}

function initNotesButtons() {
    const buttons = document.querySelectorAll('.btn-view-notes');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const rawNotas = btn.getAttribute('data-notas');
            // Escapar y reemplazar saltos de línea por tags HTML
            const htmlNotas = rawNotas.replace(/\n/g, '<br>');

            Swal.fire({
                title: '<i class="fas fa-clipboard-list text-yellow-500 mb-2 text-4xl"></i><br><span class="text-xl font-black text-gray-800 uppercase">Notas del Mecánico</span>',
                html: `
                    <div class="bg-yellow-50 text-left p-5 rounded-xl border border-yellow-200 mt-4 shadow-inner">
                        <div class="text-gray-700 text-sm font-medium leading-relaxed max-h-64 overflow-y-auto custom-scrollbar">
                            ${htmlNotas}
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Entendido',
                customClass: {
                    htmlContainer: 'm-0',
                    confirmButton: 'btn bg-gray-800 hover:bg-gray-900 border-none text-white rounded-xl w-full max-w-xs mt-4 font-bold shadow-lg shadow-gray-200',
                    popup: 'rounded-3xl border border-gray-100 shadow-2xl p-6 bg-white'
                },
                buttonsStyling: false
            });
        });
    });
}
