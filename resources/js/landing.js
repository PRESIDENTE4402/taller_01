import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    initAuth();
    initCarousel();
    initTracking();
    initScrollAnimations();
    initNavbarTransition();
    initVideoPlayer();
});

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

    window.submitBooking = (event) => {
        event.preventDefault();
        showNotification('¡Cita agendada correctamente! Un asesor BMW le contactará.', 'success');
        event.target.reset();
        window.toggleBookingModal();
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
