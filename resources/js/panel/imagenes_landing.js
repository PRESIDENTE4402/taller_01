let currentImageId = null;

// Modal Control Tailwind
window.openNewImageModal = function () {
    resetForm();
    openModal();
};

window.openModal = function () {
    const modal = document.getElementById('formModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    modal.classList.remove('hidden');

    // Timeout to allow transition
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);
};

window.closeModal = function () {
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

    setTimeout(() => {
        document.getElementById('formModal').classList.add('hidden');
        resetForm();
    }, 300);
};

// Inicializar tabla
function loadImages() {
    const typeFilter = document.getElementById('filterType').value;
    const activeFilter = document.getElementById('filterActive').value;

    fetch('/panel/mantenimientos/imagenes-landing/list')
        .then(res => res.json())
        .then(images => {
            const grid = document.querySelector('#imagesGrid');
            const emptyState = document.querySelector('#emptyState');
            grid.innerHTML = '';

            let filteredImages = images;

            if (typeFilter) {
                filteredImages = filteredImages.filter(img => img.type === typeFilter);
            }
            if (activeFilter) {
                const isActive = activeFilter === '1';
                filteredImages = filteredImages.filter(img => img.is_active === isActive);
            }

            if (filteredImages.length === 0) {
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
                grid.classList.add('hidden');
            } else {
                emptyState.classList.add('hidden');
                emptyState.classList.remove('flex');
                grid.classList.remove('hidden');

                filteredImages.forEach(img => {
                    const card = `
                        <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-slate-100 group relative flex flex-col h-full">
                            <div class="absolute top-4 right-4 z-10 flex gap-2">
                                <button class="bg-white/90 backdrop-blur text-blue-600 p-2 rounded-lg shadow-sm hover:bg-blue-50 transition-colors" onclick="editImage(${img.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="bg-white/90 backdrop-blur text-red-600 p-2 rounded-lg shadow-sm hover:bg-red-50 transition-colors" onclick="deleteImage(${img.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="relative w-full h-48 rounded-xl overflow-hidden mb-4 bg-slate-50 flex items-center justify-center">
                                ${(img.type === 'video' || img.image_url?.includes('/video/upload/'))
                            ? `<video src="${img.image_url}" class="max-w-full max-h-full object-contain" muted preload="metadata"></video>
                                       <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                           <i class="fas fa-play-circle text-white text-5xl drop-shadow-lg opacity-80"></i>
                                       </div>`
                            : `<img src="${img.image_url}" alt="${img.title}" class="max-w-full max-h-full object-contain p-2">`
                        }
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                                <div class="absolute bottom-2 left-2 flex gap-2">
                                    <span class="px-2 py-1 text-xs font-bold rounded-md bg-white/90 text-slate-800 shadow-sm backdrop-blur">
                                        ${getTypeLabel(img.type)}
                                    </span>
                                    ${img.is_active ?
                            '<span class="px-2 py-1 text-xs font-bold rounded-md bg-emerald-500 text-white shadow-sm">Activo</span>' :
                            '<span class="px-2 py-1 text-xs font-bold rounded-md bg-slate-400 text-white shadow-sm">Inactivo</span>'
                        }
                                </div>
                            </div>
                            <h4 class="font-bold text-slate-800 truncate" title="${img.title}">${img.title}</h4>
                            <p class="text-xs text-slate-500 flex-1 mt-1">
                                ${img.description || 'Sin descripción'}
                            </p>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                                <span class="flex items-center gap-1"><i class="fas fa-sort-amount-up"></i> Orden: ${img.order}</span>
                                <span>ID: #${img.id}</span>
                            </div>
                        </div>
                    `;
                    grid.innerHTML += card;
                });
            }
        });
}

// Helpers
function getTypeLabel(type) {
    const labels = {
        'logo': 'Logo Empresa',
        'service': 'Servicio',
        'gallery': 'Galería',
        'video': 'Video de Éxito',
        'about': 'Imagen Acerca De'
    };
    return labels[type] || type;
}

// Event Listeners Initialization on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    // Drag & Drop
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    if (dropZone && fileInput) {
        let isSelectingFile = false;

        dropZone.addEventListener('click', () => {
            if (isSelectingFile) return; // guard: don't re-open while choosing a file

            Swal.fire({
                title: '¿Qué deseas subir?',
                icon: 'question',
                background: '#1e293b',
                color: '#ffffff',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-image"></i>&nbsp; Imagen',
                cancelButtonText: '<i class="fas fa-video"></i>&nbsp; Video',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#8b5cf6',
                reverseButtons: false,
                returnFocus: false, // prevents focus returning to dropzone and re-triggering click
                customClass: { popup: 'border border-slate-700 rounded-xl' }
            }).then(result => {
                if (result.isConfirmed) {
                    fileInput.setAttribute('accept', 'image/jpeg,image/png,image/gif,image/webp');
                    fileInput.dataset.resourceType = 'image';
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    fileInput.setAttribute('accept', 'video/mp4,video/quicktime,video/mpeg,video/webm,video/x-msvideo');
                    fileInput.dataset.resourceType = 'video';
                } else {
                    return; // user pressed Escape / clicked backdrop — do nothing
                }
                isSelectingFile = true;
                Swal.close();
                setTimeout(() => {
                    fileInput.click();
                    setTimeout(() => { isSelectingFile = false; }, 2000); // reset after file dialog closes
                }, 200);
            });
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-blue-500', 'bg-blue-50');
                dropZone.classList.remove('border-slate-300', 'bg-slate-50');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-blue-500', 'bg-blue-50');
                dropZone.classList.add('border-slate-300', 'bg-slate-50');
            });
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            window.uploadImage();
        });

        fileInput.addEventListener('change', window.uploadImage);
    }

    // Adapt fields to type
    const imageTypeSelect = document.getElementById('imageType');
    if (imageTypeSelect) {
        imageTypeSelect.addEventListener('change', updateFormLabels);
    }

    function updateFormLabels() {
        const type = imageTypeSelect.value;
        const imageAltLabel = document.querySelector('label[for="imageAlt"]');
        const imageTitleLabel = document.querySelector('label[for="imageTitle"]');
        const imageDescriptionLabel = document.querySelector('label[for="imageDescription"]');

        if (type === 'about') {
            imageTitleLabel.innerHTML = 'Título Principal <span class="text-red-500">*</span>';
            imageAltLabel.innerHTML = 'Frase Destacada (Quote) <span class="text-red-500">*</span>';
            imageDescriptionLabel.innerHTML = 'Descripción / Historia';
            document.getElementById('imageAlt').placeholder = 'Ej: "Pasión por la Ingeniería Alemana"';
        } else {
            imageTitleLabel.innerHTML = 'Título <span class="text-red-500">*</span>';
            imageAltLabel.innerHTML = 'Texto Alternativo (SEO) <span class="text-red-500">*</span>';
            imageDescriptionLabel.innerHTML = 'Descripción';
            document.getElementById('imageAlt').placeholder = 'Ej: Logo de Tecnimecanica California';
        }
    }

    window.triggerUpdateFormLabels = updateFormLabels;

    // Limpiar imagen
    const clearBtn = document.getElementById('clearImageBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            const input = document.getElementById('fileInput');
            if (input) input.value = '';
            document.getElementById('cloudinaryPublicId').value = '';
            document.getElementById('cloudinaryUrl').value = '';
            document.getElementById('previewContainer').classList.add('hidden');
            const dropZone = document.getElementById('dropZone');
            if (dropZone) {
                dropZone.classList.remove('hidden');
                dropZone.classList.add('flex');
            }
        });
    }

    // Guardar imagen
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async () => {
            const formData = {
                type: document.getElementById('imageType').value,
                title: document.getElementById('imageTitle').value,
                description: document.getElementById('imageDescription').value,
                alt_text: document.getElementById('imageAlt').value,
                order: parseInt(document.getElementById('imageOrder').value) || 0,
                is_active: document.getElementById('imageActive').checked,
                image_url: document.getElementById('cloudinaryUrl').value,
                cloudinary_public_id: document.getElementById('cloudinaryPublicId').value
            };

            const fileInput = document.getElementById('fileInput');
            const file = fileInput ? fileInput.files[0] : null;

            if (!formData.image_url && !file) {
                Swal.fire({ title: '¡Atención!', text: 'Debes subir una imagen primero', icon: 'warning', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
                return;
            }

            if (!formData.type || !formData.title || !formData.alt_text) {
                Swal.fire({ title: '¡Atención!', text: 'Completa los campos requeridos', icon: 'warning', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
                return;
            }

            const btnText = document.getElementById('saveBtnText');
            saveBtn.disabled = true;
            btnText.innerHTML = 'Subiendo y Guardando...';
            saveBtn.classList.add('opacity-75');

            if (file) {
                const isVideoFile = file.type.startsWith('video/') || fileInput.dataset.resourceType === 'video';
                const uploadData = new FormData();
                uploadData.append('file', file);
                uploadData.append('type', formData.type);
                uploadData.append('resource_type', isVideoFile ? 'video' : 'image');

                try {
                    const uploadResponse = await fetch('/panel/mantenimientos/imagenes-landing/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: uploadData
                    });
                    const uploadResult = await uploadResponse.json();

                    if (uploadResult.success) {
                        formData.image_url = uploadResult.data.url;
                        formData.cloudinary_public_id = uploadResult.data.public_id;
                    } else {
                        Swal.fire({ title: 'Error', text: 'Error subiendo a Cloudinary: ' + uploadResult.message, icon: 'error', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
                        saveBtn.disabled = false;
                        btnText.innerHTML = 'Guardar Imagen';
                        saveBtn.classList.remove('opacity-75');
                        return;
                    }
                } catch (err) {
                    console.error('Upload Error:', err);
                    Swal.fire({ title: 'Error', text: 'Error al subir imagen', icon: 'error', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
                    saveBtn.disabled = false;
                    btnText.innerHTML = 'Guardar Imagen';
                    saveBtn.classList.remove('opacity-75');
                    return;
                }
            }

            const url = currentImageId
                ? `/panel/mantenimientos/imagenes-landing/${currentImageId}`
                : '/panel/mantenimientos/imagenes-landing';
            const method = currentImageId ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.closeModal();
                        loadImages();
                        Swal.fire({ title: '¡Éxito!', text: 'Imagen guardada correctamente', icon: 'success', background: '#1e293b', color: '#ffffff', timer: 2000, showConfirmButton: false });
                    } else {
                        let errorMsg = data.message || 'Error desconocido';
                        if (data.errors) {
                            const firstErrorKey = Object.keys(data.errors)[0];
                            errorMsg = data.errors[firstErrorKey][0];
                        }
                        Swal.fire({ title: 'Error', text: errorMsg, icon: 'error', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
                    }
                })
                .catch(err => {
                    console.error('Save Error:', err);
                    Swal.fire({ title: 'Error', text: 'Error al guardar el registro.', icon: 'error', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    btnText.innerHTML = 'Guardar Imagen';
                    saveBtn.classList.remove('opacity-75');
                });
        });
    }

    // Filtros
    const filterType = document.getElementById('filterType');
    const filterActive = document.getElementById('filterActive');

    if (filterType) filterType.addEventListener('change', loadImages);
    if (filterActive) filterActive.addEventListener('change', loadImages);

    // Cargar imágenes y sucursales al iniciar
    loadImages();
    loadBranches();
});

// Preview local (sube a Cloudinary al guardar)
window.uploadImage = function () {
    const fileInput = document.getElementById('fileInput');
    const dropZone = document.getElementById('dropZone');
    const previewContainer = document.getElementById('previewContainer');
    const file = fileInput.files[0];
    if (!file) return;

    const localUrl = URL.createObjectURL(file);
    const isVideo = file.type.startsWith('video/');

    // Clear any existing preview content
    const existingPreview = previewContainer.querySelector('#mediaPreview');
    if (existingPreview) existingPreview.remove();

    if (isVideo) {
        const video = document.createElement('video');
        video.id = 'mediaPreview';
        video.src = localUrl;
        video.controls = true;
        video.className = 'max-w-full h-48 mx-auto block rounded-lg';
        const hiddenInputsWrapper = previewContainer.querySelector('.mt-3');
        previewContainer.insertBefore(video, hiddenInputsWrapper);
        document.getElementById('imagePreview').classList.add('hidden');
    } else {
        const img = document.getElementById('imagePreview');
        img.src = localUrl;
        img.classList.remove('hidden');
    }

    previewContainer.classList.remove('hidden');
    document.getElementById('uploadStatus').textContent = isVideo
        ? 'Video listo para subir al Guardar'
        : 'Imagen lista para procesar al Guardar';
    dropZone.classList.add('hidden');
    dropZone.classList.remove('flex');
}

// Editar imagen
window.editImage = function (id) {
    fetch(`/panel/mantenimientos/imagenes-landing/list`)
        .then(res => res.json())
        .then(images => {
            const img = images.find(i => i.id === id);
            if (!img) return;

            currentImageId = id;
            document.getElementById('formTitle').textContent = 'Editar Imagen';
            document.getElementById('imageType').value = img.type;
            document.getElementById('imageTitle').value = img.title;
            document.getElementById('imageDescription').value = img.description || '';
            document.getElementById('imageAlt').value = img.alt_text || '';
            document.getElementById('imageOrder').value = img.order;
            document.getElementById('imageActive').checked = img.is_active;

            // Show preview - handle video vs image
            const isVideoRecord = img.image_url && (img.image_url.includes('/video/upload/') || img.image_url.match(/\.(mp4|webm|mov|avi)$/i));
            const previewContainer = document.getElementById('previewContainer');

            // Remove any dynamically added video preview
            const existingMediaPreview = previewContainer.querySelector('#mediaPreview');
            if (existingMediaPreview && existingMediaPreview.tagName === 'VIDEO') existingMediaPreview.remove();

            document.getElementById('imagePreview').classList.remove('hidden');

            if (isVideoRecord) {
                document.getElementById('imagePreview').classList.add('hidden');
                let vidEl = document.createElement('video');
                vidEl.id = 'mediaPreview';
                vidEl.src = img.image_url;
                vidEl.controls = true;
                vidEl.className = 'max-w-full h-48 mx-auto block rounded-lg';
                previewContainer.insertBefore(vidEl, previewContainer.querySelector('.mt-3'));
            } else {
                document.getElementById('imagePreview').src = img.image_url;
            }

            document.getElementById('cloudinaryPublicId').value = img.cloudinary_public_id;
            document.getElementById('cloudinaryUrl').value = img.image_url;
            previewContainer.classList.remove('hidden');

            const dropZone = document.getElementById('dropZone');
            if (dropZone) {
                dropZone.classList.add('hidden');
                dropZone.classList.remove('flex');
            }

            if (window.triggerUpdateFormLabels) {
                window.triggerUpdateFormLabels();
            }

            window.openModal();
        });
}

// Eliminar imagen
window.deleteImage = async function (id) {
    const result = await Swal.fire({
        title: '¿Eliminar Imagen?',
        text: "Esta acción no se puede deshacer y también se eliminará de Cloudinary.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#3b82f6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: '#0f172a',
        color: '#f8fafc',
        customClass: {
            popup: 'border border-slate-700 rounded-xl',
            confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg mr-2',
            cancelButton: 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg'
        },
        buttonsStyling: false
    });

    if (!result.isConfirmed) return;

    fetch(`/panel/mantenimientos/imagenes-landing/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadImages();
                Swal.fire({ title: 'Eliminada', text: 'La imagen ha sido eliminada', icon: 'success', background: '#1e293b', color: '#ffffff', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ title: 'Error', text: 'Error al eliminar', icon: 'error', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#3b82f6' });
            }
        });
}

// Reset form
function resetForm() {
    currentImageId = null;
    document.getElementById('imageForm').reset();
    document.getElementById('formTitle').textContent = 'Agregar Nueva Imagen';
    document.getElementById('previewContainer').classList.add('hidden');

    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    if (dropZone) {
        dropZone.classList.remove('hidden');
        dropZone.classList.add('flex');
    }
    if (fileInput) fileInput.value = '';

    if (window.triggerUpdateFormLabels) {
        window.triggerUpdateFormLabels();
    }
}

// ===== GESTIÓN DE SUCURSALES (WHATSAPP) =====
function loadBranches() {
    fetch('/panel/mantenimientos/sucursales/list')
        .then(res => res.json())
        .then(branches => {
            const container = document.getElementById('branchesContainer');
            if (!container) return;
            container.innerHTML = '';

            branches.forEach(branch => {
                const card = `
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center justify-between group hover:shadow-md transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                <i class="fab fa-whatsapp text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800">${branch.nombre}</h4>
                                <p class="text-sm text-slate-500 font-medium">${branch.telefono || 'Sin número'}</p>
                            </div>
                        </div>
                        <button onclick="editBranchPhone(${branch.id}, '${branch.nombre}', '${branch.telefono || ''}')" 
                                class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <i class="fas fa-edit text-lg"></i>
                        </button>
                    </div>
                `;
                container.innerHTML += card;
            });
        });
}

window.editBranchPhone = function (id, name, phone) {
    document.getElementById('editBranchId').value = id;
    document.getElementById('editBranchName').value = name;
    document.getElementById('editBranchPhone').value = phone;
    window.openBranchModal();
};

window.openBranchModal = function () {
    const modal = document.getElementById('branchModal');
    const backdrop = document.getElementById('branchModalBackdrop');
    const panel = document.getElementById('branchModalPanel');
    modal.classList.remove('hidden');
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4');
    }, 10);
};

window.closeBranchModal = function () {
    const backdrop = document.getElementById('branchModalBackdrop');
    const panel = document.getElementById('branchModalPanel');
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4');
    setTimeout(() => {
        document.getElementById('branchModal').classList.add('hidden');
    }, 300);
};

window.saveBranchPhone = function () {
    const id = document.getElementById('editBranchId').value;
    const phone = document.getElementById('editBranchPhone').value;

    if (!phone) {
        Swal.fire({ title: 'Atención', text: 'El número es requerido', icon: 'warning', background: '#1e293b', color: '#ffffff' });
        return;
    }

    fetch(`/panel/mantenimientos/sucursales/${id}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ telefono: phone })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.closeBranchModal();
                loadBranches();
                Swal.fire({ title: '¡Éxito!', text: 'Teléfono actualizado correctamente', icon: 'success', background: '#1e293b', color: '#ffffff', timer: 1500, showConfirmButton: false });
            } else {
                Swal.fire({ title: 'Error', text: data.message || 'Error al actualizar', icon: 'error', background: '#1e293b', color: '#ffffff' });
            }
        })
        .catch(err => {
            console.error('Error:', err);
            Swal.fire({ title: 'Error', text: 'Error de conexión', icon: 'error', background: '#1e293b', color: '#ffffff' });
        });
};
