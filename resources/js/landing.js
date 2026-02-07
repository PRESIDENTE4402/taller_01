import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    initAuth();
    initCarousel();
    initTracking();
    initScrollAnimations();
    initNavbarTransition();
    initVideoPlayer();
    initVehicleSelectors();
});

const API_CONFIG = {
    GET_BRANDS: '/panel/operaciones/citas/api/get-brands',
    GET_MODELS: '/panel/mantenimientos/modelos/by-marca',
    GET_VERSIONS: '/panel/mantenimientos/versiones/by-modelo'
};

// ===== AUTH MODAL LOGIC =====
function initAuth() {
    let isAuthenticated = false;
    const modal = document.getElementById('authModal');

    // Global functions for HTML access
    window.toggleAuthModal = () => {
        modal.classList.toggle('active');
        if (modal.classList.contains('active')) {
            modal.style.display = 'flex';
            // Small delay for fade in
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
            // If authenticated and clicking 'Agendar', open the booking modal directly
            event.preventDefault();
            window.openBookingModal();
        }
    };

    window.submitBooking = async (event) => {
        event.preventDefault();

        // Recoger datos
        const data = {
            nombre: document.getElementById('clientName').value,
            email: document.getElementById('clientEmail').value,
            telefono: document.getElementById('clientPhone').value,
            placa: document.getElementById('vehiculoPlaca').value,
            marca: document.getElementById('vehiculoMarca').value,
            modelo: document.getElementById('vehiculoModelo').value,
            version: document.getElementById('vehiculoVersion').value,
            motivo: document.getElementById('requestDetails').value,
            valet: document.getElementById('valet_service').checked,
            fecha: document.getElementById('selectedDate').value || new Date().toISOString().split('T')[0],
            hora: document.getElementById('selectedTime').value || '09:00'
        };

        // Validación Frontend
        if (!data.placa || !data.marca || !data.nombre || !data.email) {
            Swal.fire('Atención', 'Por favor complete los campos obligatorios (Contacto, Placa y Marca).', 'warning');
            return;
        }

        // UI Loading
        const btn = event.target.querySelector('button[type="submit"]');
        const originalText = btn.innerText;
        btn.innerText = 'Procesando...';
        btn.disabled = true;

        try {
            // Get CSRF Token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const response = await fetch('/api/landing/citas', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                // Validación de Laravel (422)
                if (response.status === 422) {
                    const errors = Object.values(result.errors).flat().join('\n');
                    throw new Error(errors || 'Datos inválidos');
                }
                throw new Error(result.message || 'Ocurrió un error al procesar la cita');
            }

            // Éxito
            Swal.fire({
                title: '¡Solicitud Recibida!',
                text: 'Su cita ha sido registrada. Un asesor se pondrá en contacto pronto para confirmar.',
                icon: 'success',
                confirmButtonColor: '#1C69D4',
                background: '#fff',
                color: '#333'
            });

            event.target.reset();
            window.toggleBookingModal();

        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: error.message,
                icon: 'error',
                confirmButtonColor: '#d33'
            });
        } finally {
            btn.innerText = originalText;
            btn.disabled = false;
        }
    };

    // ===== BOOKING MODAL LOGIC (PREMIUM) =====
    window.openBookingModal = () => {
        const modal = document.getElementById('bookingModal');
        modal.style.display = 'flex';
        // Add active class after a small delay to trigger transition
        setTimeout(() => modal.classList.add('active'), 10);

        // Set default date to today's date if empty
        const dateInput = document.querySelector('.premium-date-input');
        if (dateInput && !dateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }
    };

    window.toggleBookingModal = () => {
        const modal = document.getElementById('bookingModal');
        if (modal.classList.contains('active')) {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        } else {
            window.openBookingModal();
        }
    };

    // Time Selection Logic
    window.selectTime = (button, time) => {
        // Deselect all others
        document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('selected'));

        // Select clicked
        button.classList.add('selected');

        // Update hidden input
        const timeInput = document.getElementById('selectedTime');
        if (timeInput) timeInput.value = time;
    };

    window.updateTimeSlots = (date) => {
        // Here you would normally fetch available slots from backend
        // For now, we just reset the selection
        document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('selected'));
        const timeInput = document.getElementById('selectedTime');
        if (timeInput) timeInput.value = '';

        showNotification(`Horarios actualizados para ${date}`, 'success');
    };

    // Close on click outside for both modals
    window.onclick = function (event) {
        const authModal = document.getElementById('authModal');
        const bookingModal = document.getElementById('bookingModal');

        if (event.target === authModal) window.toggleAuthModal();
        // Check if clicking the overlay (which has id bookingModal)
        if (event.target === bookingModal) window.toggleBookingModal();
    };

    function showAuthenticatedSections() {
        if (document.getElementById('citas')) document.getElementById('citas').style.display = 'none';
        if (document.getElementById('seguimiento')) document.getElementById('seguimiento').style.display = 'none';
        if (document.getElementById('citas-auth')) document.getElementById('citas-auth').style.display = 'block';
        if (document.getElementById('seguimiento-auth')) document.getElementById('seguimiento-auth').style.display = 'block';
    }

    // ===== VEHICLE SELECTORS LOGIC =====
    async function initVehicleSelectors() {
        loadBrands();

        const marcaSelect = document.getElementById('vehiculoMarcaSelect');
        const modeloSelect = document.getElementById('vehiculoModeloSelect');
        const versionSelect = document.getElementById('vehiculoVersionSelect');

        if (marcaSelect) {
            marcaSelect.addEventListener('change', (e) => {
                const input = document.getElementById('vehiculoMarca');
                const val = e.target.value;
                if (val === 'otro') {
                    input.classList.remove('hidden');
                    input.value = '';
                    input.focus();
                    resetSelect(modeloSelect, 'Escriba Modelo...');
                    resetSelect(versionSelect, 'Escriba Versión...');
                } else {
                    input.classList.add('hidden');
                    input.value = e.target.options[e.target.selectedIndex].text;
                    if (val) loadModels(val);
                    else resetSelect(modeloSelect, 'Seleccione Marca...');
                }
            });
        }

        if (modeloSelect) {
            modeloSelect.addEventListener('change', (e) => {
                const input = document.getElementById('vehiculoModelo');
                const val = e.target.value;
                if (val === 'otro') {
                    input.classList.remove('hidden');
                    input.value = '';
                    input.focus();
                    resetSelect(versionSelect, 'Escriba Versión...');
                } else {
                    input.classList.add('hidden');
                    input.value = e.target.options[e.target.selectedIndex].text;
                    if (val) loadVersions(val);
                    else resetSelect(versionSelect, 'Seleccione Modelo...');
                }
            });
        }

        if (versionSelect) {
            versionSelect.addEventListener('change', (e) => {
                const input = document.getElementById('vehiculoVersion');
                const val = e.target.value;
                if (val === 'otro') {
                    input.classList.remove('hidden');
                    input.value = '';
                    input.focus();
                } else {
                    input.classList.add('hidden');
                    input.value = e.target.options[e.target.selectedIndex].text;
                }
            });
        }
    }

    async function loadBrands() {
        const select = document.getElementById('vehiculoMarcaSelect');
        if (!select) return;
        try {
            const res = await fetch(API_CONFIG.GET_BRANDS);
            const data = await res.json();
            fillSelect(select, data, 'Seleccione Marca...');
        } catch (e) { console.error(e); }
    }

    async function loadModels(marcaId) {
        const select = document.getElementById('vehiculoModeloSelect');
        if (!select) return;
        try {
            const res = await fetch(`${API_CONFIG.GET_MODELS}/${marcaId}`);
            const data = await res.json();
            fillSelect(select, data, 'Seleccione Modelo...');
        } catch (e) { console.error(e); }
    }

    async function loadVersions(modeloId) {
        const select = document.getElementById('vehiculoVersionSelect');
        if (!select) return;
        try {
            const res = await fetch(`${API_CONFIG.GET_VERSIONS}/${modeloId}`);
            const data = await res.json();
            fillSelect(select, data, 'Seleccione Versión...');
        } catch (e) { console.error(e); }
    }

    function fillSelect(select, items, defaultText) {
        select.innerHTML = `<option value="">${defaultText}</option>`;
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.nombre;
            select.appendChild(opt);
        });
        const optOtro = document.createElement('option');
        optOtro.value = 'otro';
        optOtro.textContent = '-- OTRO / MANUAL --';
        optOtro.style.fontWeight = 'bold';
        select.appendChild(optOtro);
        select.disabled = false;
    }

    function resetSelect(select, text) {
        if (!select) return;
        select.innerHTML = `<option value="">${text}</option>`;
        select.disabled = true;
    }
}

// ===== CAROUSEL LOGIC =====
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

    window.changeSlide = (n) => {
        currentSlide += n;
        window.showSlide(currentSlide);
    };

    window.currentSlideFunc = (n) => {
        currentSlide = n;
        window.showSlide(currentSlide);
    };

    // Auto rotate
    let slideInterval = setInterval(() => window.changeSlide(1), 5000);

    // Pause on hover
    const carousel = document.querySelector('.carousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', () => clearInterval(slideInterval));
        carousel.addEventListener('mouseleave', () => slideInterval = setInterval(() => window.changeSlide(1), 5000));
    }
}

// ===== TRACKING LOGIC =====
function initTracking() {
    window.searchTracking = () => {
        const code = document.getElementById('trackingCode').value;
        const result = document.getElementById('trackingResult');
        const btn = document.querySelector('.tracking-form button');

        if (code.trim() !== '') {
            // Simulate loading
            const originalText = btn.innerText;
            btn.innerText = 'Buscando...';
            btn.disabled = true;

            setTimeout(() => {
                result.style.display = 'block';
                result.style.opacity = '0';
                result.style.transform = 'translateY(20px)';

                // Animate entry
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

// ===== VIDEO PLAYER LOGIC =====
function initVideoPlayer() {
    const videos = document.querySelectorAll('video'); // Select all videos
    videos.forEach(video => {
        video.addEventListener('play', () => {
            videos.forEach(otherVideo => {
                if (otherVideo !== video) {
                    otherVideo.pause();
                }
            });
        });
    });
}

// ===== SCROLL ANIMATIONS (IntersectionObserver) =====
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('in-view');
                observer.unobserve(entry.target); // Animate once
            }
        });
    }, observerOptions);

    // Elements to animate
    const elementsToAnimate = document.querySelectorAll('.service-box, .solution-card, .story-card, .section-title, .video-card, .brand-item');
    elementsToAnimate.forEach((el, index) => {
        el.classList.add('fade-up-element');
        el.style.transitionDelay = `${index % 3 * 100}ms`; // Stagger effect
        observer.observe(el);
    });
}

// ===== NAVBAR TRANSITION =====
function initNavbarTransition() {
    const navbar = document.querySelector('.navbar');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// ===== UTILS =====
function showNotification(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerText = message;

    // Style the toast dynamically if not in CSS
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        background: type === 'success' ? '#10B981' : '#3B82F6',
        color: 'white',
        padding: '1rem 2rem',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        zIndex: '9999',
        transform: 'translateY(100px)',
        transition: 'all 0.3s ease'
    });

    document.body.appendChild(toast);

    // Animate in
    setTimeout(() => toast.style.transform = 'translateY(0)', 10);

    // Remove after 3s
    setTimeout(() => {
        toast.style.transform = 'translateY(100px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
