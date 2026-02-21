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
        setTimeout(() => modal.style.display = 'none', 300);
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
    if (authModal && event.target === authModal) window.toggleAuthModal();
    if (bookingModal && event.target === bookingModal) window.toggleBookingModal();
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
            const response = await fetch(API_CONFIG.GET_BRANCHES);
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
            const response = await fetch(API_CONFIG.GET_BRANDS);
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
                document.getElementById('vehiculoModeloSelect').classList.add('hidden');
                document.getElementById('vehiculoModelo').classList.remove('hidden');
                document.getElementById('vehiculoVersionSelect').classList.add('hidden');
                document.getElementById('vehiculoVersion').classList.remove('hidden');
                return;
            }

            // Normal behavior
            if (manualInput) manualInput.classList.add('hidden');

            if (marcaId) {
                try {
                    const response = await fetch(`${API_CONFIG.GET_MODELS}/${marcaId}`);
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
                document.getElementById('vehiculoVersionSelect').classList.add('hidden');
                document.getElementById('vehiculoVersion').classList.remove('hidden');
                return;
            }
            // Normal behavior
            if (manualInput) manualInput.classList.add('hidden');

            if (modeloId) {
                try {
                    const response = await fetch(`${API_CONFIG.GET_VERSIONS}/${modeloId}`);
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
        if(!input) {
            Swal.fire({ title: 'Atención', text: 'Ingrese un correo o teléfono para buscar.', icon: 'warning', target: document.getElementById('bookingModal') });
            return;
        }
        
        const btn = document.querySelector('button[onclick="lookupClient()"]');
        const originalText = btn.innerText;
        btn.innerText = '...';
        btn.disabled = true;

        try {
            const response = await fetch(`/api/landing/client-lookup?query=${encodeURIComponent(input)}`);
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
        } catch(error) {
            Swal.fire({ title: 'Error', text: 'No se pudo buscar la información.', icon: 'error', target: document.getElementById('bookingModal') });
        } finally {
            btn.innerText = originalText;
            btn.disabled = false;
        }
    };

    window.selectExistingVehicle = async () => {
        const select = document.getElementById('existingVehiclesSelect');
        const placaSeleccionada = select.value;

        if(!placaSeleccionada) {
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
        if(vehiculo) {
            document.getElementById('vehiculoPlaca').value = vehiculo.placa;
            document.getElementById('vehiculoPlaca').style.backgroundColor = '#dcfce7';
            setTimeout(() => document.getElementById('vehiculoPlaca').style.backgroundColor = '', 2000);
            
            // Opcional: auto-seleccionar marca/modelo si coinciden los ids
            // Para simplificar, si autocompleta la placa correctamente, el backend sabrá quién es y qué vehículo es,
            // pero le daremos "pistas" a los selects si es que existen
            const marcaSelect = document.getElementById('vehiculoMarcaSelect');
            if(vehiculo.marca_id && Array.from(marcaSelect.options).some(opt => opt.value == vehiculo.marca_id)) {
                marcaSelect.value = vehiculo.marca_id;
                marcaSelect.dispatchEvent(new Event('change'));
                
                setTimeout(() => {
                    const modelSelect = document.getElementById('vehiculoModeloSelect');
                    if(vehiculo.modelo_id && Array.from(modelSelect.options).some(opt => opt.value == vehiculo.modelo_id)) {
                        modelSelect.value = vehiculo.modelo_id;
                        modelSelect.dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            const vSelect = document.getElementById('vehiculoVersionSelect');
                            if(vehiculo.version_id && Array.from(vSelect.options).some(opt => opt.value == vehiculo.version_id)) {
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

        const response = await fetch('/api/landing/citas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(data)
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
