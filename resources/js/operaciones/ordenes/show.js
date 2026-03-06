document.addEventListener('DOMContentLoaded', () => {
    initTaskForm();
    initPartForm();
    initPartEditForm();
    initRepuestoSearch();
    initFastRepuestoForm(); // New
    initOriginButtons();
    initStatusActions();
    initDetailStatusUpdates();
    initDeletePartButtons();
    initNotesButtons();
    initEditTaskForm();
    initDeleteTaskButtons();
    initPagoForm();
    initEditPagoForm();
    initDeletePagoButtons();
});

function initStatusActions() {
    const btnFinalizar = document.querySelector('.btn-finalizar');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', async () => {
            const { isConfirmed } = await Swal.fire({
                title: '¿Finalizar Reparación?',
                text: 'Se marcará como reparada y se registrará la fecha de hoy. Lista para entrega.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn bg-green-600 hover:bg-green-700 text-white border-none rounded-xl ml-2 shadow-lg shadow-green-500/30',
                    cancelButton: 'btn btn-ghost border border-gray-200 hover:bg-gray-100 text-gray-600 rounded-xl font-bold',
                    popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                },
                buttonsStyling: false
            });

            if (isConfirmed) {
                updateStatus('finalizada');
            }
        });
    }

    const btnEntregar = document.querySelector('.btn-entregar');
    if (btnEntregar) {
        btnEntregar.addEventListener('click', async () => {
            const { isConfirmed } = await Swal.fire({
                title: '¿Entregar Vehículo?',
                text: 'Se registrará que el cliente ha recibido su vehículo. Este paso cierra la orden.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, entregar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl ml-2 shadow-lg shadow-indigo-500/30 font-black',
                    cancelButton: 'btn btn-ghost border border-gray-200 hover:bg-gray-100 text-gray-600 rounded-xl font-bold',
                    popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                },
                buttonsStyling: false
            });

            if (isConfirmed) {
                updateStatus('entregada');
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
            Swal.fire({
                title: '¡Éxito!',
                text: data.message,
                icon: 'success',
                customClass: {
                    confirmButton: 'btn bg-blue-600 hover:bg-blue-700 text-white border-none rounded-xl',
                    popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                },
                buttonsStyling: false
            }).then(() => window.location.reload());
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                    popup: 'rounded-3xl shadow-2xl bg-white p-6'
                },
                buttonsStyling: false
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudo actualizar el estado',
            icon: 'error',
            customClass: {
                confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                popup: 'rounded-3xl shadow-2xl bg-white p-6'
            },
            buttonsStyling: false
        });
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
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                    }
                });
                document.getElementById('modal-task').close();
                form.reset();
                window.location.reload(); // Re-render for simplicity or append row
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                        popup: 'rounded-3xl shadow-2xl bg-white p-6'
                    },
                    buttonsStyling: false
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo comunicar con el servidor',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                    popup: 'rounded-3xl shadow-2xl bg-white p-6'
                },
                buttonsStyling: false
            });
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
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                    }
                });
                document.getElementById('modal-part').close();
                form.reset();
                await reloadTableParts();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                        popup: 'rounded-3xl shadow-2xl bg-white p-6'
                    },
                    buttonsStyling: false
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo comunicar con el servidor',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                    popup: 'rounded-3xl shadow-2xl bg-white p-6'
                },
                buttonsStyling: false
            });
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

    const editButtons = document.querySelectorAll('.btn-origin-edit');
    const editInput = document.querySelector('#edit_suministrado_por');

    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            editButtons.forEach(b => b.classList.remove('btn-active', 'bg-opacity-20'));
            btn.classList.add('btn-active', 'bg-opacity-20');
            editInput.value = btn.dataset.value;
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
                    // Update UI without reload
                    updateRowAppearance(tr, nuevoEstado);
                    updateTotalParts();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                            popup: 'rounded-3xl shadow-2xl bg-white p-6'
                        },
                        buttonsStyling: false
                    });
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                        popup: 'rounded-3xl shadow-2xl bg-white p-6'
                    },
                    buttonsStyling: false
                });
            }
        });
    });
}

function updateRowAppearance(tr, estado) {
    const descDiv = tr.querySelector('td:nth-child(1) .font-bold');
    const totalDiv = tr.querySelector('td:nth-child(6)');
    const select = tr.querySelector('.status-detail-select');

    // Reset properties
    descDiv.classList.remove('line-through', 'text-gray-400');
    totalDiv.classList.remove('line-through', 'text-gray-400', 'opacity-50');
    select.classList.remove('text-orange-500', 'text-green-600', 'text-red-500');

    if (estado === 'rechazado') {
        descDiv.classList.add('line-through', 'text-gray-400');
        totalDiv.classList.add('line-through', 'text-gray-400', 'opacity-50');
        select.classList.add('text-red-500');
    } else if (estado === 'aprobado') {
        select.classList.add('text-green-600');
    } else {
        select.classList.add('text-orange-500');
    }
}

function updateTotalParts() {
    let sumParts = 0;
    const rows = document.querySelectorAll('#parts-table-body tr.hover\\:bg-gray-50');
    rows.forEach(tr => {
        const select = tr.querySelector('.status-detail-select');
        if (select && select.value !== 'rechazado') {
            const qtyText = tr.querySelector('td:nth-child(3)').textContent;
            const priceText = tr.querySelector('td:nth-child(4)').textContent.replace('Q.', '').replace(/,/g, '');
            const qty = parseFloat(qtyText);
            const price = parseFloat(priceText);

            if (!isNaN(qty) && !isNaN(price)) {
                sumParts += (qty * price);
            }
        }
    });

    // Update tfoot
    const tfootTotal = document.getElementById('tfoot-total');
    if (tfootTotal) {
        tfootTotal.textContent = 'Q.' + sumParts.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Update sidebar
    const sidebarPartsTotal = document.getElementById('sidebar-total-parts');
    if (sidebarPartsTotal) {
        sidebarPartsTotal.textContent = 'Q.' + sumParts.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    const sidebarTotalMain = document.getElementById('sidebar-total-main');
    if (sidebarTotalMain) {
        sidebarTotalMain.textContent = 'Q.' + sumParts.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
}

function initDeletePartButtons() {
    const buttons = document.querySelectorAll('.btn-delete-part');
    buttons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const tr = e.target.closest('tr');
            const detailId = tr.dataset.detailId;
            const url = window.location.pathname + '/details/' + detailId;

            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar repuesto?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn bg-red-600 hover:bg-red-700 text-white border-none rounded-xl ml-2 shadow-lg shadow-red-500/30',
                    cancelButton: 'btn btn-ghost border border-gray-200 hover:bg-gray-100 text-gray-600 rounded-xl font-bold',
                    popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                },
                buttonsStyling: false
            });

            if (isConfirmed) {
                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        tr.remove();
                        updateTotalParts();
                        Swal.fire({
                            title: '¡Eliminado!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                                popup: 'rounded-3xl shadow-2xl bg-white p-6'
                            },
                            buttonsStyling: false
                        });
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo eliminar el repuesto',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                            popup: 'rounded-3xl shadow-2xl bg-white p-6'
                        },
                        buttonsStyling: false
                    });
                }
            }
        });
    });
}

function initPartEditForm() {
    const form = document.getElementById('form-edit-part');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const detailId = document.getElementById('edit_part_id').value;
        const url = window.location.pathname + '/details/' + detailId;

        // Build JSON payload since it's a PUT request
        const payload = {
            suministrado_por: document.getElementById('edit_suministrado_por').value,
            descripcion_manual: document.getElementById('edit_descripcion_manual').value,
            cantidad: document.getElementById('edit_cantidad').value,
            precio_unitario: document.getElementById('edit_precio_unitario').value
        };

        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            document.getElementById('modal-edit-part').close();
            if (data.success) {
                Swal.fire({
                    title: '¡Repuesto Actualizado!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                    }
                });
                await reloadTableParts();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                        popup: 'rounded-3xl shadow-2xl bg-white p-6'
                    },
                    buttonsStyling: false
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo comunicar con el servidor',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                    popup: 'rounded-3xl shadow-2xl bg-white p-6'
                },
                buttonsStyling: false
            });
        }
    });
}

window.openEditPartModal = function (detailId, origen, descripcion, cantidad, precio) {
    document.getElementById('edit_part_id').value = detailId;
    document.getElementById('edit_descripcion_manual').value = descripcion;
    document.getElementById('edit_cantidad').value = cantidad;
    document.getElementById('edit_precio_unitario').value = precio;
    document.getElementById('edit_suministrado_por').value = origen;

    const editButtons = document.querySelectorAll('.btn-origin-edit');
    editButtons.forEach(b => b.classList.remove('btn-active', 'bg-opacity-20'));
    document.querySelector(`.btn-origin-edit[data-value="${origen}"]`).classList.add('btn-active', 'bg-opacity-20');

    document.getElementById('modal-edit-part').showModal();
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
                    `*Taller Mecánico - Cotización Orden #${codigoOrden}* 🚘\n\n` +
                    `¡Hola! 👨‍🔧 Te enviamos una actualización/cotización sobre los servicios de tu vehículo.\n\n` +
                    `📄 *Puedes ver el detalle completo de la orden aquí:*\n${linkPdf}\n\n` +
                    `¡Quedamos a la espera de tu confirmación para proceder! ✅`
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
                        title: 'Aviso',
                        text: 'La cotización debe ser confirmada. Funcionalidad de correos programada para la próxima actualización de tu sistema.',
                        icon: 'info',
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

function initRepuestoSearch() {
    const searchInput = document.getElementById('busqueda-repuesto');
    const sugerenciasUl = document.getElementById('lista-sugerencias-repuestos');
    const hiddenRepuestoId = document.getElementById('repuesto_id_hidden');
    const manualDescInput = document.querySelector('input[name="descripcion_manual"]');
    const precioInput = document.querySelector('input[name="precio_unitario"]');
    // Ruta limpia extraida de la url, cortando el ID de orden original si existe.
    // Ej: /panel/operaciones/ordenes-trabajo/12 -> /panel/operaciones/ordenes-trabajo/api/search-repuestos
    const baseUrl = window.location.pathname.replace(/\/\d+$/, '');
    const apiRoute = baseUrl + '/api/search-repuestos';

    if (!searchInput) return;

    let timeoutId;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(timeoutId);
        const term = e.target.value.trim();

        if (term.length < 2) {
            sugerenciasUl.classList.add('hidden');
            sugerenciasUl.innerHTML = '';
            hiddenRepuestoId.value = '';
            manualDescInput.value = searchInput.value; // si escribe algo y no lo elige, se asume manual
            return;
        }

        timeoutId = setTimeout(async () => {
            try {
                const response = await fetch(`${apiRoute}?term=${term}`);
                const data = await response.json();

                if (data.length > 0) {
                    // Agrupar por categoria
                    const grouped = data.reduce((acc, curr) => {
                        if (!acc[curr.categoria]) acc[curr.categoria] = [];
                        acc[curr.categoria].push(curr);
                        return acc;
                    }, {});

                    let html = '';
                    for (const [cat, items] of Object.entries(grouped)) {
                        html += `<li class="px-4 py-1.5 bg-gray-100 border-y border-gray-200 text-[10px] uppercase font-black tracking-widest text-gray-400 sticky top-0 z-10"><i class="fas fa-tag mr-1"></i> ${cat}</li>`;
                        items.forEach(item => {
                            html += `
                                <li class="px-4 py-2 cursor-pointer hover:bg-blue-50 border-b border-gray-50 last:border-0 suggestion-item flex justify-between items-center transition-colors"
                                    data-id="${item.id}"
                                    data-nombre="${item.nombre}"
                                    data-precio="${item.precio}"
                                    data-codigo="${item.codigo}">
                                    <div>
                                        <div class="font-bold text-xs text-gray-700">${item.nombre}</div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">${item.codigo}</div>
                                    </div>
                                    <div class="font-mono font-bold text-sm text-blue-600">Q.${parseFloat(item.precio).toFixed(2)}</div>
                                </li>
                            `;
                        });
                    }
                    sugerenciasUl.innerHTML = html;
                    sugerenciasUl.classList.remove('hidden');

                    // Click en las descripciones
                    document.querySelectorAll('.suggestion-item').forEach(li => {
                        li.addEventListener('click', () => {
                            searchInput.value = li.dataset.nombre;
                            hiddenRepuestoId.value = li.dataset.id;
                            manualDescInput.value = ''; // limpiar descripción manual ya que usará repuesto de stock
                            // Set precio e inutilizar description manual temporalmente o permitir al form guardar ambos, 
                            // pero el backend da prioridad al id si existe.
                            if (precioInput && li.dataset.precio > 0) {
                                precioInput.value = parseFloat(li.dataset.precio).toFixed(2);
                            }
                            sugerenciasUl.classList.add('hidden');
                        });
                    });

                } else {
                    sugerenciasUl.innerHTML = '<li class="px-4 py-4 text-sm text-gray-400 font-medium italic text-center">No se encontraron repuestos en este inventario. Puedes crearlo manualmente abajo.</li>';
                    sugerenciasUl.classList.remove('hidden');
                }
            } catch (error) {
                console.error(error);
            }
        }, 300); // Debounce de 300ms
    });

    // Ocultar si se hace click fuera
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !sugerenciasUl.contains(e.target)) {
            sugerenciasUl.classList.add('hidden');
        }
    });
}

function initFastRepuestoForm() {
    const form = document.getElementById('form-fast-repuesto');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = {
            nombre: document.getElementById('fast_nombre').value,
            precio_venta: document.getElementById('fast_precio').value,
            stock_actual: document.getElementById('fast_stock').value
        };

        const baseUrl = window.location.pathname.replace(/\/\d+$/, '');
        const apiRoute = baseUrl + '/api/fast-repuesto';

        // Change button state
        const submitBtn = form.querySelector('button[type="submit"]');
        const ogText = submitBtn.innerText;
        submitBtn.innerHTML = '<span class="loading loading-spinner"></span> Guardando...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(apiRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: '¡Pieza Creada!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6' }
                });

                // Set into search
                document.getElementById('busqueda-repuesto').value = data.repuesto.nombre;
                document.getElementById('repuesto_id_hidden').value = data.repuesto.id;
                document.querySelector('input[name="descripcion_manual"]').value = ''; // Clean

                // Select Taller mode dynamically if it exists
                document.querySelectorAll('.btn-origin').forEach(b => b.classList.remove('btn-active', 'bg-opacity-20'));
                const tallerBtn = document.querySelector('.btn-origin[data-value="taller"]');
                if (tallerBtn) {
                    tallerBtn.classList.add('btn-active', 'bg-opacity-20');
                    document.querySelector('input[name="suministrado_por"]').value = 'taller';
                }

                // Set Unit Price
                const precioInput = document.querySelector('input[name="precio_unitario"]');
                if (precioInput) {
                    precioInput.value = parseFloat(data.repuesto.precio).toFixed(2);
                }

                document.getElementById('modal-fast-repuesto').close();
                form.reset();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                        popup: 'rounded-3xl shadow-2xl bg-white p-6'
                    },
                    buttonsStyling: false
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo crear el repuesto rápido',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gray-800 text-white rounded-xl',
                    popup: 'rounded-3xl shadow-2xl bg-white p-6'
                },
                buttonsStyling: false
            });
        } finally {
            submitBtn.innerText = ogText;
            submitBtn.disabled = false;
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

window.openEditTaskModal = function (taskId, userId, descripcion, metaMinutos, precioCliente, descuentoCliente, motivoDescuento, tipoPago, valorPago) {
    document.getElementById('edit-task-id').value = taskId;
    document.getElementById('edit-task-user_id').value = userId;
    document.getElementById('edit-task-descripcion').value = descripcion;
    document.getElementById('edit-task-meta_minutos').value = metaMinutos || 0;
    document.getElementById('edit-task-precio').value = precioCliente || 0;
    document.getElementById('edit-task-descuento').value = descuentoCliente || 0;
    document.getElementById('edit-task-motivo_descuento').value = motivoDescuento;
    document.getElementById('edit-task-tipo_pago').value = tipoPago || 'porcentaje';
    document.getElementById('edit-task-valor_pago').value = valorPago || 0;
    document.getElementById('modal-edit-task').showModal();
};

function initEditTaskForm() {
    const form = document.getElementById('form-edit-task');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const taskId = document.getElementById('edit-task-id').value;
        const formData = new FormData(form);
        const url = window.location.pathname + '/tasks/' + taskId;

        try {
            const response = await fetch(url, {
                method: 'POST', // Usamos POST porque _method=PUT ya va en FormData
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            document.getElementById('modal-edit-task').close();

            if (data.success) {
                Swal.fire({
                    title: '¡Tarea Actualizada!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6' }
                });

                // Actualizar la fila discretamente
                const btn = document.querySelector(`.btn-delete-task[data-task-id="${taskId}"]`);
                if (btn) {
                    const tr = btn.closest('tr');
                    const nuevaDesc = document.getElementById('edit-task-descripcion').value;
                    const descDiv = tr.querySelector('.font-black.text-gray-800.text-sm.italic');
                    if (descDiv) descDiv.innerText = nuevaDesc;

                    const nuevoMinutos = document.getElementById('edit-task-meta_minutos').value;
                    const minSpan = tr.querySelector('.badge-ghost.font-mono');
                    if (minSpan) minSpan.innerText = nuevoMinutos + ' MIN';
                }
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: { confirmButton: 'btn bg-gray-800 text-white rounded-xl', popup: 'rounded-3xl shadow-2xl bg-white p-6' },
                    buttonsStyling: false
                });
            }
        } catch (error) {
            document.getElementById('modal-edit-task').close();
            console.error(error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo comunicar con el servidor',
                icon: 'error',
                customClass: { confirmButton: 'btn bg-gray-800 text-white rounded-xl', popup: 'rounded-3xl shadow-2xl bg-white p-6' },
                buttonsStyling: false
            });
        }
    });
}

function initDeleteTaskButtons() {
    const btns = document.querySelectorAll('.btn-delete-task');
    btns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const taskId = e.currentTarget.closest('button').dataset.taskId;
            const url = window.location.pathname + '/tasks/' + taskId;

            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar tarea?',
                text: 'Se borrará por completo y sus cobros.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn bg-red-600 hover:bg-red-700 text-white border-none rounded-xl ml-2 shadow-lg shadow-red-500/30',
                    cancelButton: 'btn btn-ghost border border-gray-200 hover:bg-gray-100 text-gray-600 rounded-xl font-bold',
                    popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6'
                },
                buttonsStyling: false
            });

            if (isConfirmed) {
                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        e.currentTarget.closest('tr').remove(); // remove row from table without reload
                        Swal.fire({
                            title: '¡Eliminada!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-3xl shadow-2xl border border-gray-100 bg-white p-6' }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            customClass: { confirmButton: 'btn bg-gray-800 text-white rounded-xl', popup: 'rounded-3xl shadow-2xl bg-white p-6' },
                            buttonsStyling: false
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error de servidor',
                        icon: 'error',
                        customClass: { confirmButton: 'btn bg-gray-800 text-white rounded-xl', popup: 'rounded-3xl shadow-2xl bg-white p-6' },
                        buttonsStyling: false
                    });
                }
            }
        });
    });
}

async function reloadTableParts() {
    try {
        const response = await fetch(window.location.href);
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const replaceElement = (id) => {
            const curr = document.getElementById(id);
            const next = doc.getElementById(id);
            if (curr && next) curr.innerHTML = next.innerHTML;
        };

        replaceElement('parts-table-body');
        replaceElement('tfoot-total');
        replaceElement('sidebar-total-parts');
        replaceElement('sidebar-total-main');

        // Re-bind events to new elements
        initDetailStatusUpdates();
        initDeletePartButtons();
    } catch (err) {
        console.error('Error reloading table', err);
        window.location.reload(); // fallback
    }
}

function initPagoForm() {
    const form = document.getElementById('form-pago');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const url = window.location.pathname + '/pagos';

        // Check if monto <= 0
        const monto = parseFloat(formData.get('monto'));
        if (isNaN(monto) || monto <= 0) {
            Swal.fire({
                title: 'Error',
                text: 'El monto debe ser mayor a 0',
                icon: 'error',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }

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
                    title: '¡Pago Registrado!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl shadow-2xl bg-white p-6' }
                });
                document.getElementById('modal-pago').close();
                form.reset();
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: { popup: 'rounded-3xl bg-white p-6' }
                });
            }
        } catch (error) {
            console.error(error);
            Swal.fire({
                title: 'Error',
                text: 'No se pudo registrar el pago',
                icon: 'error',
                customClass: { popup: 'rounded-3xl bg-white p-6' }
            });
        }
    });
}

window.openEditPagoModal = function (pagoId, monto, metodo) {
    document.getElementById('edit_pago_id').value = pagoId;
    document.getElementById('edit_pago_monto').value = monto;
    document.getElementById('edit_pago_metodo').value = metodo;
    document.getElementById('modal-edit-pago').showModal();
}

function initEditPagoForm() {
    const form = document.getElementById('form-edit-pago');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const pagoId = document.getElementById('edit_pago_id').value;
        const formData = new FormData(form);
        const url = window.location.pathname + '/pagos/' + pagoId;

        // Check if monto <= 0
        const monto = parseFloat(formData.get('monto'));
        if (isNaN(monto) || monto <= 0) {
            Swal.fire({
                title: 'Error',
                text: 'El monto debe ser mayor a 0',
                icon: 'error',
                customClass: { popup: 'rounded-3xl' }
            });
            return;
        }

        try {
            const response = await fetch(url, {
                method: 'POST', // we use _method=PUT natively via Laravel rules but JS is easier treating as POST or letting fetch do PUT. FormData handles `_method` gracefully.
                headers: {
                    // Send CSRF only
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: '¡Pago Actualizado!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-3xl shadow-2xl bg-white p-6' }
                });
                document.getElementById('modal-edit-pago').close();
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: data.message,
                    icon: 'error',
                    customClass: { popup: 'rounded-3xl bg-white p-6' }
                });
            }
        } catch (err) {
            console.error(err);
            Swal.fire({
                title: 'Error',
                text: 'Fallo al contactar al servidor',
                icon: 'error'
            });
        }
    });
}

function initDeletePagoButtons() {
    const buttons = document.querySelectorAll('.btn-delete-pago');
    buttons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const pagoId = e.currentTarget.dataset.pagoId;
            const url = window.location.pathname + '/pagos/' + pagoId;

            const { isConfirmed } = await Swal.fire({
                title: '¿Eliminar Pago?',
                text: 'El monto volverá a ser parte de la deuda.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    confirmButton: 'btn bg-red-600 hover:bg-red-700 text-white border-none rounded-xl ml-2',
                    cancelButton: 'btn btn-ghost font-bold text-gray-500 rounded-xl',
                    popup: 'rounded-3xl shadow-2xl bg-white p-6'
                },
                buttonsStyling: false
            });

            if (isConfirmed) {
                try {
                    const response = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-3xl shadow-2xl bg-white p-6' }
                        });
                        window.location.reload(); // Reload to recalculate
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message,
                            icon: 'error',
                            customClass: { popup: 'rounded-3xl bg-white p-6' }
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error de servidor',
                        icon: 'error',
                        customClass: { popup: 'rounded-3xl bg-white p-6' }
                    });
                }
            }
        });
    });
}
