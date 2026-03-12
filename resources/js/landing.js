import './bootstrap';

// Configuration
const API_CONFIG = {
    GET_BRANDS: '/api/landing/brands',
    GET_MODELS: '/api/landing/models',
    GET_VERSIONS: '/api/landing/versions',
    GET_BRANCHES: '/api/landing/branches'
};

document.addEventListener('DOMContentLoaded', () => {
    initAuth();
    initCarousel();
    initTracking();
    initScrollAnimations();
    initNavbarTransition();
    initVideoPlayer();
    loadLandingImages();
    if (window.initVehicleSelectors) window.initVehicleSelectors();
});

// ===== AUTH MODAL LOGIC =====
function initAuth() {
    let isAuthenticated = false;
    const modal = document.getElementById('authModal');

    window.toggleAuthModal = () => {
        if (!modal) return;
        modal.classList.toggle('active');
        if (modal.classList.contains('active')) {
            modal.style.display = 'flex';
            setTimeout(() => modal.style.opacity = '1', 10);
        } else {
            modal.style.opacity = '0';
            setTimeout(() => modal.style.display = 'none', 300);
        }
    };

    window.switchAuthTab = (event, tab) => {
        event.preventDefault();
        document.querySelectorAll('.auth-tab').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.auth-tab-content').forEach(content => content.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById(tab + '-tab').classList.add('active');
    };

    window.submitLogin = (event) => {
        event.preventDefault();
        isAuthenticated = true;
        window.toggleAuthModal();
        showAuthenticatedSections();
        showNotification('¡Bienvenido! Sesión iniciada correctamente', 'success');
    };

    window.submitRegister = (event) => {
        event.preventDefault();
        isAuthenticated = true;
        window.toggleAuthModal();
        showAuthenticatedSections();
        showNotification('¡Bienvenido! Cuenta creada correctamente', 'success');
    };

    window.checkAuth = (event, section) => {
        if (!isAuthenticated) {
            event.preventDefault();
            window.toggleAuthModal();
        } else if (section === 'citas') {
            event.preventDefault();
            window.openBookingModal();
        }
    };
}

// ===== BOOKING MODAL LOGIC (PREMIUM) =====
window.openBookingModal = () => {
    const modal = document.getElementById('bookingModal');
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);

    // Default date
    const dateInput = document.querySelector('.premium-date-input');
    if (dateInput && !dateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }
};

window.toggleBookingModal = () => {
    const modal = document.getElementById('bookingModal');
    if (!modal) return;
    if (modal.classList.contains('active')) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            window.resetBookingForm();
        }, 300);
    } else {
        window.openBookingModal();
    }
};

window.selectTime = (button, time) => {
    document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('selected'));
    button.classList.add('selected');
    const timeInput = document.getElementById('selectedTime');
    if (timeInput) timeInput.value = time;
};

window.updateTimeSlots = (date) => {
    document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('selected'));
    const timeInput = document.getElementById('selectedTime');
    if (timeInput) timeInput.value = '';
    showNotification(`Horarios actualizados para ${date}`, 'success');
};

window.onclick = function (event) {
    const authModal = document.getElementById('authModal');
    const bookingModal = document.getElementById('bookingModal');
    const whatsappModal = document.getElementById('whatsappModal');

    if (authModal && event.target === authModal) window.toggleAuthModal();
    if (bookingModal && event.target === bookingModal) window.toggleBookingModal();
    if (whatsappModal && event.target === whatsappModal) window.closeWhatsAppModal();
};

window.resetBookingForm = () => {
    const form = document.getElementById('bookingForm');
    if (!form) return;
    form.reset();

    // Reset hidden inputs
    const selectedTime = document.getElementById('selectedTime');
    if (selectedTime) selectedTime.value = '';

    // Reset time slots
    document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('selected'));

    // Reset vehicle selectors state
    ['Marca', 'Modelo', 'Version'].forEach(type => {
        const select = document.getElementById(`vehiculo${type}Select`);
        const manual = document.getElementById(`vehiculo${type}`);
        const btn = document.getElementById(`btnBack${type}`);

        if (select) {
            select.classList.remove('hidden');
            select.value = '';
            if (type !== 'Marca') select.disabled = true;
        }
        if (manual) manual.classList.add('hidden');
        if (btn) btn.classList.add('hidden');
    });

    // Reset Branch
    const sucursalSelect = document.getElementById('sucursalSelect');
    if (sucursalSelect) sucursalSelect.value = '';

    // Reset Lookup
    const lookupInput = document.getElementById('lookupInput');
    if (lookupInput) lookupInput.value = '';

    const container = document.getElementById('existingVehiclesContainer');
    const existingSelect = document.getElementById('existingVehiclesSelect');
    if (container) container.style.display = 'none';
    if (existingSelect) existingSelect.innerHTML = '<option value="">-- Ignorar / Registrar nuevo vehículo --</option>';
    window.clientVehiclesCache = [];

    // Reset styles
    document.querySelectorAll('#bookingForm input, #bookingForm select, #bookingForm textarea').forEach(el => {
        el.style.border = '';
        el.style.backgroundColor = '';
    });
};

window.backToSelect = (type) => {
    const select = document.getElementById(`vehiculo${type}Select`);
    const manual = document.getElementById(`vehiculo${type}`);
    const btn = document.getElementById(`btnBack${type}`);

    if (select) {
        select.classList.remove('hidden');
        select.value = '';
        select.dispatchEvent(new Event('change'));
    }
    if (manual) {
        manual.classList.add('hidden');
        manual.value = '';
    }
    if (btn) btn.classList.add('hidden');
};

function showAuthenticatedSections() {
    if (document.getElementById('citas')) document.getElementById('citas').style.display = 'none';
    if (document.getElementById('seguimiento')) document.getElementById('seguimiento').style.display = 'none';
    if (document.getElementById('citas-auth')) document.getElementById('citas-auth').style.display = 'block';
    if (document.getElementById('seguimiento-auth')) document.getElementById('seguimiento-auth').style.display = 'block';
}

// ===== BOOKING LOGIC =====
window.initVehicleSelectors = async () => {
    const brandSelect = document.getElementById('vehiculoMarcaSelect');
    const modelSelect = document.getElementById('vehiculoModeloSelect');
    const versionSelect = document.getElementById('vehiculoVersionSelect');
    const branchSelect = document.getElementById('sucursalSelect');

    // Fetch Branches
    if (branchSelect) {
        try {
            const response = await fetch(API_CONFIG.GET_BRANCHES, { credentials: 'same-origin' });
            const branches = await response.json();
            branchSelect.innerHTML = '<option value="">Seleccione Taller / Sucursal...</option>';
            branches.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch.id;
                option.textContent = branch.nombre;
                branchSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error fetching branches:', error);
        }
    }

    // Fetch Brands
    if (brandSelect) {
        try {
            const response = await fetch(API_CONFIG.GET_BRANDS, { credentials: 'same-origin' });
            const brands = await response.json();
            brands.push({ id: 'otro', nombre: '-- OTRO / MANUAL --' });
            brandSelect.innerHTML = '<option value="">Seleccione Marca...</option>';
            brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.id;
                option.textContent = brand.nombre;
                brandSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error fetching brands:', error);
        }

        brandSelect.addEventListener('change', async function () {
            const marcaId = this.value;
            const manualInput = document.getElementById('vehiculoMarca');
            modelSelect.innerHTML = '<option value="">Seleccione Marca...</option>';
            modelSelect.disabled = true;
            versionSelect.innerHTML = '<option value="">Seleccione Modelo...</option>';
            versionSelect.disabled = true;

            if (marcaId === 'otro') {
                this.classList.add('hidden');
                manualInput.classList.remove('hidden');
                manualInput.focus();
                document.getElementById('btnBackMarca').classList.remove('hidden');

                document.getElementById('vehiculoModeloSelect').classList.add('hidden');
                document.getElementById('vehiculoModelo').classList.remove('hidden');
                document.getElementById('vehiculoVersionSelect').classList.add('hidden');
                document.getElementById('vehiculoVersion').classList.remove('hidden');
                return;
            }

            // Normal behavior
            if (manualInput) manualInput.classList.add('hidden');
            const btnBackMarca = document.getElementById('btnBackMarca');
            if (btnBackMarca) btnBackMarca.classList.add('hidden');

            // Restore selects visibility if they were hidden by "Otro"
            const mSelect = document.getElementById('vehiculoModeloSelect');
            const vSelect = document.getElementById('vehiculoVersionSelect');
            const mManual = document.getElementById('vehiculoModelo');
            const vManual = document.getElementById('vehiculoVersion');
            const mBtn = document.getElementById('btnBackModelo');
            const vBtn = document.getElementById('btnBackVersion');

            if (mSelect) mSelect.classList.remove('hidden');
            if (vSelect) vSelect.classList.remove('hidden');
            if (mManual) mManual.classList.add('hidden');
            if (vManual) vManual.classList.add('hidden');
            if (mBtn) mBtn.classList.add('hidden');
            if (vBtn) vBtn.classList.add('hidden');

            if (marcaId) {
                try {
                    const response = await fetch(`${API_CONFIG.GET_MODELS}/${marcaId}`, { credentials: 'same-origin' });
                    const models = await response.json();
                    models.push({ id: 'otro', nombre: '-- OTRO / MANUAL --' });
                    modelSelect.innerHTML = '<option value="">Seleccione Modelo...</option>';
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model.id;
                        option.textContent = model.nombre;
                        modelSelect.appendChild(option);
                    });
                    modelSelect.disabled = false;
                } catch (error) { console.error(error); }
            }
        });
    }

    if (modelSelect) {
        modelSelect.addEventListener('change', async function () {
            const modeloId = this.value;
            const manualInput = document.getElementById('vehiculoModelo');
            if (modeloId === 'otro') {
                this.classList.add('hidden');
                manualInput.classList.remove('hidden');
                manualInput.focus();
                document.getElementById('btnBackModelo').classList.remove('hidden');
                
                document.getElementById('vehiculoVersionSelect').classList.add('hidden');
                document.getElementById('vehiculoVersion').classList.remove('hidden');
                return;
            }
            // Normal behavior
            if (manualInput) manualInput.classList.add('hidden');
            const btnBackModelo = document.getElementById('btnBackModelo');
            if (btnBackModelo) btnBackModelo.classList.add('hidden');

            const vSelectEx = document.getElementById('vehiculoVersionSelect');
            const vManualEx = document.getElementById('vehiculoVersion');
            const vBtnEx = document.getElementById('btnBackVersion');

            if (vSelectEx) vSelectEx.classList.remove('hidden');
            if (vManualEx) vManualEx.classList.add('hidden');
            if (vBtnEx) vBtnEx.classList.add('hidden');

            if (modeloId) {
                try {
                    const response = await fetch(`${API_CONFIG.GET_VERSIONS}/${modeloId}`, { credentials: 'same-origin' });
                    const versions = await response.json();
                    versions.push({ id: 'otro', nombre: '-- OTRO / MANUAL --' });
                    versionSelect.innerHTML = '<option value="">Seleccione Versión...</option>';
                    versions.forEach(version => {
                        const option = document.createElement('option');
                        option.value = version.id;
                        option.textContent = version.nombre;
                        versionSelect.appendChild(option);
                    });
                    versionSelect.disabled = false;
                } catch (error) { console.error(error); }
            }
        });
    }

    if (versionSelect) {
        versionSelect.addEventListener('change', function () {
            if (this.value === 'otro') {
                this.classList.add('hidden');
                document.getElementById('vehiculoVersion').classList.remove('hidden');
                document.getElementById('vehiculoVersion').focus();
                document.getElementById('btnBackVersion').classList.remove('hidden');
            } else {
                const val = document.getElementById('vehiculoVersion');
                if (val) val.classList.add('hidden');
            }
        });
    }
};

window.clientVehiclesCache = [];

window.lookupClient = async () => {
    const input = document.getElementById('lookupInput').value.trim();
    if (!input) {
        Swal.fire({ title: 'Atención', text: 'Ingrese un correo o teléfono para buscar.', icon: 'warning', target: document.getElementById('bookingModal') });
        return;
    }

    const btn = document.querySelector('button[onclick="lookupClient()"]');
    const originalText = btn.innerText;
    btn.innerText = '...';
    btn.disabled = true;

    try {
        const response = await fetch(`/api/landing/client-lookup?query=${encodeURIComponent(input)}`, { credentials: 'same-origin' });
        const data = await response.json();

        if (response.ok && data.success) {
            // Rellenar datos cliente
            document.getElementById('clientName').value = data.cliente.nombre;
            document.getElementById('clientEmail').value = data.cliente.email;
            document.getElementById('clientPhone').value = data.cliente.telefono;

            // Vehículos
            const container = document.getElementById('existingVehiclesContainer');
            const select = document.getElementById('existingVehiclesSelect');

            if (data.vehiculos && data.vehiculos.length > 0) {
                window.clientVehiclesCache = data.vehiculos;
                select.innerHTML = '<option value="">-- Ignorar / Registrar nuevo vehículo --</option>';

                data.vehiculos.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.placa;
                    opt.textContent = `${v.placa} - ${v.detalles_texto}`;
                    select.appendChild(opt);
                });

                container.style.display = 'block';
                Swal.fire({ title: '¡Hola de nuevo!', text: 'Hemos cargado tus datos. Selecciona tu vehículo.', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, target: document.getElementById('bookingModal') });
            } else {
                container.style.display = 'none';
                Swal.fire({ title: '¡Hola de nuevo!', text: 'Datos cargados. Por favor registra tu vehículo.', icon: 'success', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, target: document.getElementById('bookingModal') });
            }

            // Efecto de parpadeo verde para indicar autocompletado
            ['clientName', 'clientEmail', 'clientPhone'].forEach(id => {
                const el = document.getElementById(id);
                el.style.backgroundColor = '#dcfce7';
                setTimeout(() => el.style.backgroundColor = '', 2000);
            });
        } else {
            Swal.fire({ title: 'No encontrado', text: data.message || 'No existe cliente con esos datos.', icon: 'info', target: document.getElementById('bookingModal') });
        }
    } catch (error) {
        Swal.fire({ title: 'Error', text: 'No se pudo buscar la información.', icon: 'error', target: document.getElementById('bookingModal') });
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
};

window.selectExistingVehicle = async () => {
    const select = document.getElementById('existingVehiclesSelect');
    const placaSeleccionada = select.value;

    if (!placaSeleccionada) {
        // Limpiar campos para modo manual
        document.getElementById('vehiculoPlaca').value = '';
        document.getElementById('vehiculoMarcaSelect').value = '';
        document.getElementById('vehiculoModeloSelect').value = '';
        document.getElementById('vehiculoVersionSelect').value = '';

        // Trigger change
        document.getElementById('vehiculoMarcaSelect').dispatchEvent(new Event('change'));
        return;
    }

    const vehiculo = window.clientVehiclesCache.find(v => v.placa === placaSeleccionada);
    if (vehiculo) {
        document.getElementById('vehiculoPlaca').value = vehiculo.placa;
        document.getElementById('vehiculoPlaca').style.backgroundColor = '#dcfce7';
        setTimeout(() => document.getElementById('vehiculoPlaca').style.backgroundColor = '', 2000);

        // Opcional: auto-seleccionar marca/modelo si coinciden los ids
        // Para simplificar, si autocompleta la placa correctamente, el backend sabrá quién es y qué vehículo es,
        // pero le daremos "pistas" a los selects si es que existen
        const marcaSelect = document.getElementById('vehiculoMarcaSelect');
        if (vehiculo.marca_id && Array.from(marcaSelect.options).some(opt => opt.value == vehiculo.marca_id)) {
            marcaSelect.value = vehiculo.marca_id;
            marcaSelect.dispatchEvent(new Event('change'));

            setTimeout(() => {
                const modelSelect = document.getElementById('vehiculoModeloSelect');
                if (vehiculo.modelo_id && Array.from(modelSelect.options).some(opt => opt.value == vehiculo.modelo_id)) {
                    modelSelect.value = vehiculo.modelo_id;
                    modelSelect.dispatchEvent(new Event('change'));

                    setTimeout(() => {
                        const vSelect = document.getElementById('vehiculoVersionSelect');
                        if (vehiculo.version_id && Array.from(vSelect.options).some(opt => opt.value == vehiculo.version_id)) {
                            vSelect.value = vehiculo.version_id;
                        }
                    }, 500);
                }
            }, 500);
        }
    }
};

window.submitBooking = async (event) => {
    event.preventDefault();
    const data = {
        nombre: document.getElementById('clientName').value,
        email: document.getElementById('clientEmail').value,
        telefono: document.getElementById('clientPhone').value,
        placa: document.getElementById('vehiculoPlaca').value,
        marca: document.getElementById('vehiculoMarca').value,
        modelo: document.getElementById('vehiculoModelo').value,
        version: document.getElementById('vehiculoVersion').value,
        motivo: document.getElementById('requestDetails').value,
        sucursal_id: document.getElementById('sucursalSelect') ? document.getElementById('sucursalSelect').value : null,
        fecha: document.getElementById('selectedDate').value,
        hora: ''
    };

    const activeTimeBtn = document.querySelector('.time-slot.selected');
    if (activeTimeBtn) data.hora = activeTimeBtn.innerText;

    const isManualMarca = document.getElementById('vehiculoMarcaSelect').value === 'otro';
    const isManualModelo = document.getElementById('vehiculoModeloSelect').value === 'otro' || document.getElementById('vehiculoModeloSelect').classList.contains('hidden');
    const isManualVersion = document.getElementById('vehiculoVersionSelect').value === 'otro' || document.getElementById('vehiculoVersionSelect').classList.contains('hidden');

    const getSelectText = (id) => {
        const el = document.getElementById(id);
        if (!el || el.selectedIndex === -1 || el.value === '' || el.value === 'otro') return '';
        return el.options[el.selectedIndex].text;
    };

    if (!isManualMarca) data.marca = getSelectText('vehiculoMarcaSelect');
    if (!isManualModelo) data.modelo = getSelectText('vehiculoModeloSelect');
    if (!isManualVersion) data.version = getSelectText('vehiculoVersionSelect');

    const resetStyles = () => {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.form-control-error').forEach(el => el.classList.remove('form-control-error'));
        document.querySelectorAll('#bookingForm input, #bookingForm select, #bookingForm textarea').forEach(el => el.style.border = '');
        const timeGrid = document.getElementById('timeSlotsGrid');
        if (timeGrid) timeGrid.style.border = '';
    };
    resetStyles();

    const markError = (id, message) => {
        let el = document.getElementById(id);
        if (el) el.style.border = '2px solid #ff4444';
        Swal.fire({ title: 'Atención', text: message, icon: 'warning', target: document.getElementById('bookingModal') });
    };

    const markErrorElement = (el, message) => {
        if (el) { el.style.border = '2px solid #ff4444'; el.focus(); }
        Swal.fire({ title: 'Atención', text: message, icon: 'warning', target: document.getElementById('bookingModal') });
    };

    if (!data.nombre) { markError('clientName', 'Por favor ingrese su Nombre Completo.'); return; }
    if (!data.email) { markError('clientEmail', 'Por favor ingrese su Correo Electrónico.'); return; }
    if (!data.telefono) { markError('clientPhone', 'Por favor ingrese su Teléfono Móvil.'); return; }
    if (!data.placa) { markError('vehiculoPlaca', 'Por favor ingrese la Placa del vehículo.'); return; }

    if (!data.marca) {
        const el = isManualMarca ? document.getElementById('vehiculoMarca') : document.getElementById('vehiculoMarcaSelect');
        markErrorElement(el, 'Por favor seleccione o ingrese la Marca del vehículo.');
        return;
    }

    if (!data.modelo) {
        const el = isManualModelo ? document.getElementById('vehiculoModelo') : document.getElementById('vehiculoModeloSelect');
        markErrorElement(el, 'Por favor seleccione o ingrese el Modelo del vehículo.');
        return;
    }
    if (!data.version) {
        const el = isManualVersion ? document.getElementById('vehiculoVersion') : document.getElementById('vehiculoVersionSelect');
        markErrorElement(el, 'Por favor seleccione o ingrese la Versión del vehículo.');
        return;
    }
    if (!data.motivo) { markError('requestDetails', 'Por favor describa el tipo de requerimiento o servicio.'); return; }
    if (!data.sucursal_id) { markError('sucursalSelect', 'Por favor seleccione el Taller / Sucursal de preferencia.'); return; }
    if (!data.fecha) { markErrorElement(document.getElementById('selectedDate'), 'Por favor seleccione una fecha preferida.'); return; }
    if (!data.hora) {
        const timeGrid = document.getElementById('timeSlotsGrid');
        if (timeGrid) timeGrid.style.border = '2px solid #ff4444';
        Swal.fire({ title: 'Atención', text: 'Por favor seleccione un horario disponible.', icon: 'warning', target: document.getElementById('bookingModal') });
        return;
    }

    const confirmResult = await Swal.fire({
        title: '¿Confirmar Cita?',
        html: `¿Estás seguro de agendar para el día <b>${data.fecha}</b> a las <b>${data.hora}</b>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, Agendar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1C69D4',
        cancelButtonColor: '#6c757d',
        target: document.getElementById('bookingModal')
    });

    if (!confirmResult.isConfirmed) return;

    try {
        const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerText = 'PROCESANDO...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/api/landing/citas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ...data,
                _token: csrfToken
            }),
            credentials: 'same-origin'
        });

        const result = await response.json();

        if (response.ok && result.success) {
            Swal.fire({
                title: '¡Cita Agendada!',
                text: 'Hemos recibido tu solicitud correctamente. Te contactaremos pronto para confirmar.',
                icon: 'success',
                confirmButtonColor: '#1C69D4',
                target: document.getElementById('bookingModal')
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(result.message || 'Ocurrió un error al procesar la solicitud.');
        }
    } catch (error) {
        console.error('Error submitting booking:', error);
        Swal.fire({
            title: 'Error',
            text: error.message || 'No se pudo agendar la cita. Por favor, inténtelo de nuevo.',
            icon: 'error',
            confirmButtonColor: '#1C69D4',
            target: document.getElementById('bookingModal')
        });
    } finally {
        const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
        submitBtn.disabled = false;
        submitBtn.innerText = 'AGENDAR CITA';
    }
};

// ... Carousel, Tracking, Video, Scroll, Navbar code ...
function initCarousel() {
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-item');
    const indicators = document.querySelectorAll('.indicator');
    if (slides.length === 0) return;

    window.showSlide = (n) => {
        if (n >= slides.length) currentSlide = 0;
        if (n < 0) currentSlide = slides.length - 1;
        slides.forEach(slide => slide.classList.remove('active'));
        indicators.forEach(ind => ind.classList.remove('active'));
        slides[currentSlide].classList.add('active');
        if (indicators[currentSlide]) indicators[currentSlide].classList.add('active');
    };
    window.changeSlide = (n) => { currentSlide += n; window.showSlide(currentSlide); };
    window.currentSlideFunc = (n) => { currentSlide = n; window.showSlide(currentSlide); };
    let slideInterval = setInterval(() => window.changeSlide(1), 5000);
    const carousel = document.querySelector('.carousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', () => clearInterval(slideInterval));
        carousel.addEventListener('mouseleave', () => slideInterval = setInterval(() => window.changeSlide(1), 5000));
    }
}

function initTracking() {
    window.searchTracking = () => {
        const code = document.getElementById('trackingCode').value;
        const result = document.getElementById('trackingResult');
        const btn = document.querySelector('.tracking-form button');
        if (code.trim() !== '') {
            const originalText = btn.innerText;
            btn.innerText = 'Buscando...';
            btn.disabled = true;
            setTimeout(() => {
                result.style.display = 'block';
                result.style.opacity = '0';
                result.style.transform = 'translateY(20px)';
                requestAnimationFrame(() => {
                    result.style.transition = 'all 0.5s ease';
                    result.style.opacity = '1';
                    result.style.transform = 'translateY(0)';
                });
                btn.innerText = originalText;
                btn.disabled = false;
            }, 800);
        }
    };
}

function initVideoPlayer() {
    const videos = document.querySelectorAll('video');
    videos.forEach(video => {
        video.addEventListener('play', () => {
            videos.forEach(otherVideo => {
                if (otherVideo !== video) otherVideo.pause();
            });
        });
    });
}

function initScrollAnimations() {
    const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    const elementsToAnimate = document.querySelectorAll('.service-box, .solution-card, .story-card, .section-title, .video-card, .brand-item');
    elementsToAnimate.forEach((el, index) => {
        el.classList.add('fade-up-element');
        el.style.transitionDelay = `${index % 3 * 100}ms`;
        observer.observe(el);
    });
}

function initNavbarTransition() {
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
    });
}

function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerText = message;
    Object.assign(toast.style, {
        position: 'fixed', bottom: '20px', right: '20px',
        background: type === 'success' ? '#10B981' : '#3B82F6',
        color: 'white', padding: '1rem 2rem', borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)', zIndex: '9999',
        transform: 'translateY(100px)', transition: 'all 0.3s ease'
    });
    document.body.appendChild(toast);
    setTimeout(() => toast.style.transform = 'translateY(0)', 10);
    setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ===== DYNAMIC LANDING IMAGES LOADER =====
async function loadLandingImages() {
    try {
        const response = await fetch('/api/landing/images', { credentials: 'same-origin' });
        if (!response.ok) throw new Error('Error loading images');

        const images = await response.json();

        // Separate images by type
        const logoImage = images.find(img => img.type === 'logo' && img.is_active);
        const aboutImage = images.find(img => img.type === 'about' && img.is_active);
        const serviceImages = images.filter(img => img.type === 'service' && img.is_active);
        const galleryImages = images.filter(img => img.type === 'gallery' && img.is_active);
        const videoImages = images.filter(img => img.type === 'video' && img.is_active);
        const solutionImages = images.filter(img => img.type === 'solution' && img.is_active);
        const clientImages = images.filter(img => img.type === 'client' && img.is_active);
        const contactImages = images.filter(img => img.type === 'contact' && img.is_active);

        // Load logo
        if (logoImage) {
            updateNavbarLogo(logoImage);
        }

        // Load about image
        if (aboutImage) {
            updateAboutImage(aboutImage);
        }

        // Load services
        if (serviceImages.length > 0) {
            loadServiceCards(serviceImages);
        }

        // Load gallery carousel
        if (galleryImages.length > 0) {
            loadGalleryCarousel(galleryImages);
        }

        // Load video stories
        if (videoImages.length > 0) {
            loadVideoStories(videoImages);
        }

        // Load soluciones
        if (solutionImages.length > 0) {
            loadSolutions(solutionImages);
        } else {
            const solContainer = document.getElementById('solutions-container-dynamic');
            if(solContainer) solContainer.innerHTML = '<p style="text-align:center;width:100%;">No hay soluciones registradas.</p>';
        }

        // Load clientes
        if (clientImages.length > 0) {
            loadClients(clientImages);
        } else {
            const cliContainer = document.getElementById('clients-container-dynamic');
            if(cliContainer) cliContainer.innerHTML = '<p style="text-align:center;width:100%;">No hay clientes registrados.</p>';
        }

        // Load contacto
        if (contactImages.length > 0) {
            loadContacts(contactImages);
        } else {
            const contContainer = document.getElementById('contact-container');
            if(contContainer) contContainer.innerHTML = '<p style="text-align:center;color:white;width:100%;">No hay datos de contacto registrados.</p>';
        }
    } catch (error) {
        console.error('Error loading landing images:', error);
        showToast('Error al cargar las imágenes', 'error');
    }
}

function loadSolutions(images) {
    const container = document.getElementById('solutions-container-dynamic');
    if (!container) return;
    container.innerHTML = '';
    images.forEach(img => {
        const card = document.createElement('div');
        card.className = 'service-card-premium';
        card.innerHTML = `
            <div class="card-image" style="background-image: url('${img.image_url}');"></div>
            <div class="card-content">
                <div class="card-icon"><i class="fas fa-check-circle" style="color:var(--m-blue-light);"></i></div>
                <h3>${img.title}</h3>
                <div style="margin-bottom: 1.5rem; font-size: 0.95rem; color: #64748b;">${img.description || ''}</div>
            </div>
        `;
        container.appendChild(card);
    });
}

function loadClients(images) {
    const container = document.getElementById('clients-container-dynamic');
    if (!container) return;
    container.innerHTML = '';
    images.forEach(img => {
        const item = document.createElement('div');
        item.style.minWidth = '250px';
        item.style.flex = '0 0 auto';
        item.className = 'gallery-card';
        item.innerHTML = `
            <img src="${img.image_url}" alt="${img.alt_text}" style="border-radius:16px; object-fit:cover; width:100%; height:200px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);">
            <div style="padding: 1.5rem; text-align:center;">
                <h3 style="font-weight: 900; color: #1e293b;">${img.title}</h3>
                <div style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem; font-style: italic;">"${img.alt_text}"</div>
            </div>
        `;
        container.appendChild(item);
    });
}

function loadContacts(images) {
    const container = document.getElementById('contact-container');
    if (!container) return;
    
    // Si hay un contenedor de header extra por ahí que interfiere, lo reconstruímos limpio
    container.innerHTML = '<h2 style="color: white; font-size: 2.5rem; font-weight: 900; margin-bottom: 2rem; width: 100%; text-align:center;">Información de Contacto</h2>';
    const grid = document.createElement('div');
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(auto-fit, minmax(280px, 1fr))';
    grid.style.gap = '2rem';
    grid.style.width = '100%';

    images.forEach(img => {
        const item = document.createElement('div');
        item.style.background = 'rgba(255,255,255,0.05)';
        item.style.border = '1px solid rgba(255,255,255,0.1)';
        item.style.borderRadius = '24px';
        item.style.padding = '2rem';
        item.style.color = 'white';
        item.style.display = 'flex';
        item.style.flexDirection = 'column';
        item.style.alignItems = 'center';
        item.style.textAlign = 'center';

        item.innerHTML = `
            <div style="width: 80px; height: 80px; border-radius: 50%; overflow:hidden; margin-bottom: 1.5rem; border: 3px solid rgba(255,255,255,0.2);">
                <img src="${img.image_url}" alt="${img.title}" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <h3 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem; color: #60a5fa;">${img.title}</h3>
            <div style="color: #cbd5e1; font-size: 0.95rem; line-height: 1.6;">${img.description || ''}</div>
        `;
        grid.appendChild(item);
    });
    
    container.appendChild(grid);
}

function loadServiceCards(services) {
    const container = document.getElementById('services-container');
    if (!container) return;

    // Clear loading spinner
    container.innerHTML = '';

    // Generate service cards
    services.forEach((service, index) => {
        const card = document.createElement('div');
        card.className = 'service-card-premium';
        card.innerHTML = `
            <div class="card-image" style="background-image: url('${service.image_url}');">
            </div>
            <div class="card-content">
                <div class="card-icon">🛠️</div>
                <h3>${service.title}</h3>
                <p>${service.description}</p>
                <a href="#contacto" class="link-arrow">
                    ${index === 0 ? 'Agendar Servicio' : index === 1 ? 'Ver Detalles' : 'Consultar'}
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        `;
        container.appendChild(card);
    });
}

function loadGalleryItems(galleryImages) {
    const container = document.getElementById('gallery-container');
    if (!container) return;

    // Clear loading spinner
    container.innerHTML = '';

    // Generate gallery items with alternating sizes
    galleryImages.forEach((image, index) => {
        const item = document.createElement('div');

        // First item: big, last item: wide, others: normal
        let itemClass = 'success-item';
        if (index === 0) {
            itemClass += ' big';
        } else if (index === galleryImages.length - 1 && galleryImages.length > 2) {
            itemClass += ' wide';
        }

        item.className = itemClass;
        item.innerHTML = `
            <img src="${image.image_url}" alt="${image.alt_text}" loading="lazy">
            <div class="overlay-info">
                <h3>${image.title}</h3>
                <p>${image.description}</p>
            </div>
        `;
        container.appendChild(item);
    });
}

function updateAboutImage(image) {
    const aboutImg = document.querySelector('.about-image img');
    if (aboutImg) {
        aboutImg.src = image.image_url;
        aboutImg.alt = image.alt_text || 'Sobre Nosotros';
    }

    const textContainer = document.querySelector('.about-text');
    if (!textContainer) return;

    const title = textContainer.querySelector('h2');
    if (title && image.title) {
        title.textContent = image.title;
    }

    const highlight = textContainer.querySelector('.highlight-text');
    if (highlight && image.alt_text) {
        highlight.textContent = image.alt_text;
    }

    const description = textContainer.querySelector('p:not(.highlight-text)');
    if (description && image.description) {
        description.textContent = image.description;
    }
}

function loadGalleryCarousel(images) {
    const container = document.getElementById('gallery-container');
    if (!container) return;

    container.innerHTML = '';
    images.forEach(img => {
        const item = document.createElement('div');
        item.className = 'gallery-item-premium';
        item.innerHTML = `
            <div class="gallery-card" onclick="openGalleryModal('${img.image_url.replace(/'/g, "\\'")}', '${img.title.replace(/'/g, "\\'")}', '${(img.description || '').replace(/'/g, "\\'").replace(/\n/g, "<br>")}')">
                <img src="${img.image_url}" alt="${img.alt_text}" loading="lazy">
                <div class="gallery-info">
                    <h3>${img.title}</h3>
                    <p>${img.description || ''}</p>
                </div>
            </div>
        `;
        container.appendChild(item);
    });

    initCarouselLogic();
}

// ===== GALLERY MODAL (RESPONSIVE + VER MÁS) =====
window.openGalleryModal = function (imageUrl, title, description) {
    const modal    = document.getElementById('galleryModal');
    const imgEl    = document.getElementById('galleryModalImage');
    const titleEl  = document.getElementById('galleryModalTitle');
    const bodyEl   = document.getElementById('galleryModalBody');

    if (!modal) return;

    if (imgEl)   imgEl.src = imageUrl;
    if (titleEl) titleEl.textContent = title;

    // Lógica de 'Ver más'
    const DESC_LIMIT = 220;
    const rawText = (description || '').replace(/<br\s*\/?>/gi, '\n');

    if (bodyEl) {
        if (rawText.length <= DESC_LIMIT) {
            bodyEl.innerHTML = `<p style="margin:0;line-height:1.7;">${description || 'Sin descripción.'}</p>`;
        } else {
            const shortHtml = rawText.substring(0, DESC_LIMIT).replace(/\n/g, '<br>');
            const fullHtml  = rawText.replace(/\n/g, '<br>');
            bodyEl.innerHTML = `
                <p id="gm-short" style="margin:0;line-height:1.7;">${shortHtml}<span style="color:#94a3b8;">...</span></p>
                <p id="gm-full"  style="margin:0;line-height:1.7;display:none;">${fullHtml}</p>
                <button id="gm-toggle"
                    style="margin-top:10px;background:none;border:1px solid #1C69D4;color:#1C69D4;border-radius:8px;padding:5px 14px;font-size:0.82rem;font-weight:700;cursor:pointer;">
                    <i class='fas fa-chevron-down' style='margin-right:5px;'></i> Ver más
                </button>
            `;
            let exp = false;
            document.getElementById('gm-toggle').addEventListener('click', () => {
                exp = !exp;
                document.getElementById('gm-short').style.display = exp ? 'none' : '';
                document.getElementById('gm-full').style.display  = exp ? '' : 'none';
                document.getElementById('gm-toggle').innerHTML = exp
                    ? "<i class='fas fa-chevron-up' style='margin-right:5px;'></i> Ver menos"
                    : "<i class='fas fa-chevron-down' style='margin-right:5px;'></i> Ver más";
            });
        }
    }

    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
};

window.closeGalleryModal = function () {
    const modal = document.getElementById('galleryModal');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => { modal.style.display = 'none'; }, 300);
};

function initCarouselLogic() {
    const track = document.getElementById('gallery-container');
    const prevBtn = document.getElementById('gallery-prev');
    const nextBtn = document.getElementById('gallery-next');
    if (!track || !prevBtn || !nextBtn) return;

    let autoScrollInterval;

    const getScrollAmount = () => {
        const item = track.querySelector('.gallery-item-premium');
        return item ? item.offsetWidth : track.offsetWidth;
    };

    const scrollNext = () => {
        // Threshold of 10px
        if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
            track.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
        }
    };

    const scrollPrev = () => {
        if (track.scrollLeft <= 10) {
            track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
        } else {
            track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
        }
    };

    const startAutoScroll = () => {
        clearInterval(autoScrollInterval);
        autoScrollInterval = setInterval(scrollNext, 5000);
    };

    const stopAutoScroll = () => {
        clearInterval(autoScrollInterval);
    };

    nextBtn.addEventListener('click', () => {
        stopAutoScroll();
        scrollNext();
        startAutoScroll();
    });

    prevBtn.addEventListener('click', () => {
        stopAutoScroll();
        scrollPrev();
        startAutoScroll();
    });

    track.addEventListener('mouseenter', stopAutoScroll);
    track.addEventListener('mouseleave', startAutoScroll);

    startAutoScroll();
}

function loadVideoStories(videos) {
    const container = document.getElementById('videos-container');
    if (!container) return;

    container.innerHTML = '';
    videos.forEach(video => {
        const card = document.createElement('div');
        card.className = 'video-card-premium';
        // Si es URL de Cloudinary, a veces se puede renderizar como video tag
        const isVideo = video.image_url.match(/\.(mp4|webm|ogg|mov)$/i) || video.image_url.includes('/video/upload/');

        card.innerHTML = `
            <div class="video-media-wrapper">
                ${isVideo
                ? `<video src="${video.image_url}" controls></video>`
                : `<img src="${video.image_url}" alt="${video.alt_text}">`
            }
            </div>
            <div class="video-info-premium">
                <h3>${video.title}</h3>
                <p>${video.description || ''}</p>
            </div>
        `;
        container.appendChild(card);
    });
}

function updateNavbarLogo(logoImage) {
    const logoContainer = document.querySelector('.navbar-logo');
    if (!logoContainer) return;

    const img = logoContainer.querySelector('img');
    if (img) {
        img.src = logoImage.image_url;
        img.alt = logoImage.alt_text || 'Company Logo';
    }
}

// ===== HISTORIAL LOGIC =====
window.openHistorialModal = () => {
    const modal = document.getElementById('historialModal');
    if (!modal) return;
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('active'), 10);
};

window.toggleHistorialModal = () => {
    const modal = document.getElementById('historialModal');
    if (!modal) return;
    if (modal.classList.contains('active')) {
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
    } else {
        window.openHistorialModal();
    }
};

let currentHistorialData = null;

window.searchHistorial = async () => {
    const placa = document.getElementById('hPlaca').value.trim();
    const password = document.getElementById('hPassword').value.trim();
    const content = document.getElementById('historialContent');
    const nav = document.getElementById('historialNav');

    if (!placa || !password) {
        Swal.fire({
            title: 'Datos Incompletos',
            text: 'Por favor, ingrese la placa y su contraseña de seguridad para continuar.',
            icon: 'warning',
            target: document.getElementById('historialModal'),
            confirmButtonColor: '#1e293b'
        });
        return;
    }

    content.innerHTML = `
        <div class="no-data-msg">
            <div class="loading loading-spinner loading-lg text-primary"></div>
            <p class="mt-6 font-black text-slate-400">Sincronizando expedientes técnicos...</p>
        </div>
    `;
    if (nav) nav.classList.add('hidden');

    try {
        const url = `/api/landing/historial?placa=${encodeURIComponent(placa)}&password=${encodeURIComponent(password)}`;
        const response = await fetch(url);
        const data = await response.json();

        if (response.ok && data.success) {
            currentHistorialData = data;
            renderVehicleSelection(data.vehiculos);
        } else {
            content.innerHTML = `
                <div class="no-data-msg">
                    <i class="fas fa-triangle-exclamation text-6xl mb-6 text-orange-400"></i>
                    <h3 class="text-2xl font-black text-slate-800">${data.message || 'Error de Autenticación'}</h3>
                    <p class="text-slate-400 mt-2">Verifique sus credenciales o contacte a soporte técnico.</p>
                </div>
            `;
        }
    } catch (error) {
        content.innerHTML = `<div class="p-8 text-center text-red-500 font-bold font-black">ERROR CRÍTICO: No se pudo establecer conexión con el núcleo del sistema.</div>`;
    }
};

function renderVehicleSelection(vehiculos) {
    const content = document.getElementById('historialContent');
    const nav = document.getElementById('historialNav');
    if (nav) nav.classList.add('hidden');

    let html = `
        <div class="mb-10">
            <span class="text-blue-600 font-black text-xs uppercase tracking-[0.2em] mb-2 block">Paso 1: Seleccione su Unidad</span>
            <h3 class="text-3xl font-black text-slate-900 tracking-tight">Vehículos Vinculados</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    `;

    vehiculos.forEach((v, index) => {
        html += `
            <div class="vehicle-selection-card group" onclick="viewVehicleTimeline(${index})">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-50 transition-colors">
                    <i class="fas fa-car-side text-2xl text-slate-300 group-hover:text-blue-600 transition-colors"></i>
                </div>
                <h4 class="text-2xl font-black text-slate-900 mb-1 tracking-tighter">${v.placa}</h4>
                <p class="text-slate-400 font-bold text-sm uppercase mb-6">${v.marca?.nombre} ${v.modelo?.nombre}</p>
                <div class="flex items-center justify-center gap-2 text-blue-600 font-black text-xs uppercase tracking-widest">
                    Ver Historial <i class="fas fa-chevron-right text-[10px]"></i>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    content.innerHTML = html;
}

window.viewVehicleTimeline = (index) => {
    const v = currentHistorialData.vehiculos[index];
    const content = document.getElementById('historialContent');
    const nav = document.getElementById('historialNav');
    const navText = document.getElementById('navDetailText');

    if (nav) {
        nav.classList.remove('hidden');
        nav.classList.add('flex');
    }
    if (navText) navText.innerText = `EXPEDIENTE: ${v.placa}`;

    let html = `
        <div class="historial-grid">
            <!-- Vehicle Sidebar Card -->
            <div class="vehicle-side-card">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-white/10 rounded-xl flex items-center justify-center">
                        <i class="fas fa-car text-xl text-blue-400"></i>
                    </div>
                    <div>
                        <span class="text-blue-400 text-[10px] font-black uppercase tracking-widest block">Unidad Registrada</span>
                        <h2 class="text-3xl font-black tracking-tighter">${v.placa}</h2>
                    </div>
                </div>

                <div class="space-y-6 pt-8 border-t border-white/10">
                    <div class="flex flex-col gap-1">
                        <span class="text-blue-300/50 text-[10px] font-black uppercase tracking-widest">Marca / Modelo</span>
                        <span class="font-black text-lg">${v.marca?.nombre} ${v.modelo?.nombre}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-blue-300/50 text-[10px] font-black uppercase tracking-widest">Versión / Año</span>
                        <span class="font-black text-lg">${v.version?.nombre || 'N/A'} - ${v.anio || 'N/A'}</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-blue-300/50 text-[10px] font-black uppercase tracking-widest">Servicios Realizados</span>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-3 py-1 bg-blue-500 rounded-full font-black text-sm">${v.ordenes.length}</span>
                            <span class="text-xs text-blue-300 font-bold">Órdenes de Trabajo</span>
                        </div>
                    </div>
                </div>

                <div class="mt-12 p-6 bg-white/5 rounded-2xl border border-white/5">
                    <p class="text-[10px] text-blue-200 uppercase font-black tracking-widest mb-2"><i class="fas fa-circle-check text-blue-400 mr-2"></i> Estado de Garantía</p>
                    <p class="text-xs text-blue-100/70 leading-relaxed">Este historial técnico certifica los servicios preventivos realizados en nuestro centro autorizado.</p>
                </div>
            </div>

            <!-- Timeline Section -->
            <div class="timeline">
                ${v.ordenes.length > 0 ? v.ordenes.map(orden => `
                    <div class="timeline-item">
                        <div class="timeline-card">
                            <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4 mb-6">
                                <div>
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="badge-status-h bg-slate-100 text-slate-700">Orden #${orden.codigo_orden}</span>
                                        <span class="badge-status-h ${getStatusClass(orden.estado)}">${orden.estado.replace('_', ' ')}</span>
                                    </div>
                                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                                        <i class="fas fa-calendar-days text-slate-300"></i> ${new Date(orden.fecha_recepcion).toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' })}
                                    </h4>
                                </div>
                                <div class="bg-blue-50 px-6 py-3 rounded-2xl border border-blue-100 lg:text-right">
                                    <span class="text-[10px] font-black text-blue-400 uppercase block tracking-widest mb-1">Recorrido</span>
                                    <span class="font-black text-2xl text-blue-900">${orden.kilometraje_entrada.toLocaleString()} <span class="text-sm">KM</span></span>
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 mb-6">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 block">Descripción del Reporte</span>
                                <p class="text-slate-700 italic font-medium leading-relaxed">"${orden.falla_cliente}"</p>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                                <div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-boxes-stacked text-[10px] text-blue-600"></i>
                                        </div>
                                        <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Insumos y Repuestos</span>
                                    </div>
                                    <ul class="space-y-3">
                                        ${orden.detalles.length > 0 ? orden.detalles.map(d => `
                                            <li class="flex items-start gap-3 p-3 bg-white rounded-xl border border-slate-100 shadow-sm">
                                                <span class="w-8 h-8 bg-slate-50 rounded-lg flex items-center justify-center font-black text-slate-600 text-xs">${d.cantidad}</span>
                                                <div class="flex-1">
                                                    <p class="text-xs font-black text-slate-800 uppercase leading-none mb-1">${d.repuesto?.nombre || d.descripcion_manual}</p>
                                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tighter">Material Técnico Certificado</p>
                                                </div>
                                            </li>
                                        `).join('') : `
                                            <li class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Sin materiales reportados</span>
                                            </li>
                                        `}
                                    </ul>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-4">
                                        <div class="w-6 h-6 bg-orange-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-screwdriver-wrench text-[10px] text-orange-600"></i>
                                        </div>
                                        <span class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Labores Técnicas</span>
                                    </div>
                                    <ul class="space-y-3">
                                        ${orden.bitacoras.length > 0 ? orden.bitacoras.map(t => `
                                            <li class="flex items-start gap-3 p-3 bg-white rounded-xl border border-slate-100 shadow-sm">
                                                <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-check text-[10px] text-orange-400"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-xs font-black text-slate-800 uppercase leading-relaxed">${t.descripcion}</p>
                                                </div>
                                            </li>
                                        `).join('') : `
                                            <li class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase">Sin tareas reportadas</span>
                                            </li>
                                        `}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('') : `
                    <div class="no-data-msg">
                        <i class="fas fa-folder-open text-4xl mb-4 opacity-20"></i>
                        <p class="font-black text-slate-400 uppercase text-xs tracking-widest">No se registran intervenciones para esta unidad.</p>
                    </div>
                `}
            </div>
        </div>
    `;
    content.innerHTML = html;
};

window.backToVehicles = () => {
    if (currentHistorialData) {
        renderVehicleSelection(currentHistorialData.vehiculos);
    }
};

function getStatusClass(status) {
    const classes = {
        'PENDIENTE': 'bg-orange-100 text-orange-700',
        'PROCESO': 'bg-blue-100 text-blue-700',
        'PAUSADO': 'bg-red-100 text-red-700',
        'FINALIZADO': 'bg-green-100 text-green-700',
        'ENTREGADO': 'bg-slate-900 text-white',
        'CONFIRMACION_PRESUPUESTO': 'bg-purple-100 text-purple-700',
        'ESPERANDO_REPUESTO': 'bg-yellow-100 text-yellow-700'
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
}

// ===== WHATSAPP LOGIC =====
window.closeWhatsAppModal = () => {
    const modal = document.getElementById('whatsappModal');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = 'none', 300);
};

window.openWhatsAppModal = () => {
    const modal = document.getElementById('whatsappModal');
    if (!modal) return;
    if (modal.classList.contains('active')) {
        window.closeWhatsAppModal();
    } else {
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
        loadWhatsAppBranches();
    }
};

async function loadWhatsAppBranches() {
    const listContainer = document.getElementById('whatsappBranchesList');
    if (!listContainer) return;

    try {
        const response = await fetch('/api/landing/branches');
        const branches = await response.json();

        if (branches.length === 0) {
            listContainer.innerHTML = '<p class="text-center">No hay sucursales disponibles.</p>';
            return;
        }

        listContainer.innerHTML = '';
        branches.forEach(branch => {
            const phone = branch.telefono || '50233970404'; // Fallback
            // Limpiar el número de espacios o guiones
            const cleanPhone = phone.replace(/[^0-9]/g, '');
            const message = encodeURIComponent('Hola, me gustaría solicitar información técnica para mi vehículo.');
            const url = `https://wa.me/${cleanPhone}?text=${message}`;

            const btn = document.createElement('a');
            btn.href = url;
            btn.target = '_blank';
            btn.className = 'whatsapp-branch-btn';
            btn.innerHTML = `
                <span>${branch.nombre}</span>
                <i class="fab fa-whatsapp"></i>
            `;
            btn.onclick = () => window.closeWhatsAppModal();
            listContainer.appendChild(btn);
        });
    } catch (error) {
        console.error('Error loading branches for WhatsApp:', error);
        listContainer.innerHTML = '<p class="text-center text-red-500">Error al cargar sucursales.</p>';
    }
}

// ===== MOBILE NAV HAMBURGER =====
window.toggleNavMenu = function () {
    const panel   = document.getElementById('mobileMenuPanel');
    const overlay = document.getElementById('mobileMenuOverlay');
    const btn     = document.getElementById('hamburgerBtn');
    if (!panel) return;
    const isOpen = panel.classList.contains('open');
    if (isOpen) {
        panel.classList.remove('open');
        overlay.classList.remove('open');
        btn   && btn.classList.remove('open');
        document.body.style.overflow = '';
    } else {
        panel.classList.add('open');
        overlay.classList.add('open');
        btn   && btn.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closeNavMenu = function () {
    const panel   = document.getElementById('mobileMenuPanel');
    const overlay = document.getElementById('mobileMenuOverlay');
    const btn     = document.getElementById('hamburgerBtn');
    if (!panel) return;
    panel.classList.remove('open');
    overlay.classList.remove('open');
    btn   && btn.classList.remove('open');
    document.body.style.overflow = '';
};

