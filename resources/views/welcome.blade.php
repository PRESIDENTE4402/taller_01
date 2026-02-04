<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TECNIMECANICA CALIFORNIA - Servicios Automotrices Profesionales</title>
    <meta name="description"
        content="Servicio profesional de mantenimiento y reparación automotriz con más de 15 años de experiencia">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>

<body>
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <div class="navbar-inner">
            <div class="navbar-logo">
                <img src="{{ asset('logo-m.jpg') }}" alt="BMW M Logo" style="height: 40px; margin-right: 10px;">
                <span>TECNIMECANICA</span>
            </div>

            <div class="navbar-menu">
                <a href="#servicios" class="navbar-link">Servicios</a>
                <a href="#galeria" class="navbar-link">Nuestros éxitos</a>
                <a href="#soluciones" class="navbar-link">Soluciones</a>
                <a href="#clientes" class="navbar-link">Clientes</a>
                <a href="#contacto" class="navbar-link">Contacto</a>
            </div>

            <div class="navbar-nav">
                <a href="#citas" onclick="checkAuth(event, 'citas')" class="navbar-link secondary">Agendar</a>
                <button class="btn btn-primary" onclick="toggleAuthModal()">Ingresar</button>
            </div>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <section class="hero">
        <div class="hero-inner">
            <h1 style="text-shadow: 0 4px 10px rgba(0,0,0,0.5);">TALLERES ESPECIALIZADOS PARA SU BMW</h1>
            <p class="hero-subtitle" style="text-shadow: 0 2px 5px rgba(0,0,0,0.5);">Mantenimiento con técnicos
                capacitados, equipos especializados y repuestos originales que cumplen todos los estándares de fábrica.
            </p>
            <div class="hero-buttons">
                <a href="#citas" onclick="checkAuth(event, 'citas')" class="btn btn-primary">Agendar Cita</a>
                <a href="#contacto" class="btn btn-secondary">Cotizar Servicio</a>
            </div>
        </div>
    </section>

    <section class="trust">
        <p>Trusted by <strong>500+ satisfied customers</strong> | ISO Certified | 98% satisfaction</p>
    </section>


    <section id="servicios" class="services">
        <div class="services-inner">
            <h2>Nuestros Servicios Principales</h2>
            <div class="services-grid">
                <div class="service-box">
                    <h3>Reparación & Mantenimiento</h3>
                    <p>Servicios completos de reparación, diagnóstico y mantenimiento preventivo con técnicos
                        certificados.</p>
                    <a href="#soluciones" class="service-link">Explorar →</a>
                </div>
                <div class="service-box">
                    <h3>Diagnóstico Electrónico</h3>
                    <p>Diagnosis computarizada de sistemas eléctricos y electrónicos con equipos de última generación.
                    </p>
                    <a href="#soluciones" class="service-link">Explorar →</a>
                </div>
            </div>
        </div>
    </section>
    <section id="galeria" class="gallery">
        <div class="gallery-container">
            <div class="section-title">
                <h2>Nuestros Trabajos Realizados</h2>
                <p>Revisa las reparaciones que hemos completado con éxito</p>
            </div>

            <div class="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://res.cloudinary.com/dcfodug6m/image/upload/v1768881736/WhatsApp_Image_2026-01-19_at_8.46.46_PM_je6mv6.jpg"
                            alt="Reparación de motor">
                        <div class="carousel-caption">
                            <h4>Programación de llaves</h4>
                            <p>BMW X5 G05 2020 programación de segunda llave ahora es posible</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://res.cloudinary.com/dcfodug6m/image/upload/v1768881857/WhatsApp_Image_2026-01-19_at_8.47.39_PM_wgqejr.jpg"
                            alt="Cambio de neumáticos">
                        <div class="carousel-caption">
                            <h4>Reparación BDC2</h4>
                            <p>Reparación BDC2 BMW G01 después de programación abortada se restauró y funcionado al 💯
                            </p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://res.cloudinary.com/dcfodug6m/image/upload/v1768882416/WhatsApp_Image_2026-01-19_at_8.48.17_PM_e0p4f2.jpg"
                            alt="Diagnóstico electrónico">
                        <div class="carousel-caption">
                            <h4>Diagnóstico Electrónico</h4>
                            <p>Diagnosis completo de sistemas eléctricos</p>
                        </div>
                    </div>
                    <div class="carousel-item">
                        <img src="https://res.cloudinary.com/dcfodug6m/image/upload/v1768882560/WhatsApp_Image_2026-01-19_at_8.49.29_PM_mr3mzj.jpg"
                            alt="Mantenimiento">
                        <div class="carousel-caption">
                            <h4>Mantenimiento Correctivo </h4>
                            <p>🔋Reparación de Tesla Model 3 con problemas de carga el vehículo no aceptaba carga.</p>
                        </div>
                    </div>
                </div>

                <button class="carousel-control prev" onclick="changeSlide(-1)">&#10094;</button>
                <button class="carousel-control next" onclick="changeSlide(1)">&#10095;</button>

                <div class="carousel-indicators">
                    <span class="indicator active" onclick="currentSlideFunc(0)"></span>
                    <span class="indicator" onclick="currentSlideFunc(1)"></span>
                    <span class="indicator" onclick="currentSlideFunc(2)"></span>
                    <span class="indicator" onclick="currentSlideFunc(3)"></span>
                </div>
            </div>
        </div>
    </section>


    <section id="videos" class="videos-section">
        <div class="videos-container">
            <div class="section-title">
                <h2>Más historias de éxito</h2>
                <p>Mira algunos de nuestros trabajos más destacados</p>
            </div>

            <div class="videos-grid">
                <div class="video-card">
                    <div class="video-wrapper">
                        <video class="video-player" width="100%" height="100%" controls>
                            <source
                                src="https://res.cloudinary.com/dcfodug6m/video/upload/v1768883590/WhatsApp_Video_2026-01-19_at_10.30.39_PM_jax7da.mp4"
                                type="video/mp4">

                        </video>
                    </div>
                    <div class="video-info">
                        <h3>Reparación de Tesla Model 3</h3>
                        <p>Pioneros en la reparación de vehículos eléctricos a nivel mundial.</p>
                    </div>
                </div>

                <div class="video-card">
                    <div class="video-wrapper">
                        <video class="video-player" width="100%" height="100%" controls>
                            <source
                                src="https://res.cloudinary.com/dcfodug6m/video/upload/v1768883995/WhatsApp_Video_2026-01-19_at_10.39.03_PM_x3oskq.mp4"
                                type="video/mp4">

                        </video>
                    </div>
                    <div class="video-info">
                        <h3>Mantenimiento correctivo BMW</h3>
                        <p>Reparación BDC2 BMW G01 restaurado al 100%.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ===== BRANDS ===== -->
    <section class="brands">
        <div class="brands-inner">
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/toyota/000000" alt="Toyota"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Toyota</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/honda/000000" alt="Honda"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Honda</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/mazda/000000" alt="Mazda"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Mazda</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/hyundai/000000" alt="Hyundai"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Hyundai</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/chevrolet/000000" alt="Chevrolet"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Chevrolet</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/kia/000000" alt="Kia"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Kia</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/tesla/000000" alt="Tesla"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>Tesla</span>'">
            </div>
            <div class="brand-item">
                <img src="https://cdn.simpleicons.org/bmw/000000" alt="BMW"
                    onerror="this.onerror=null; this.parentElement.innerHTML='<span>BMW</span>'">
            </div>
        </div>
    </section>


    <!-- ===== SOLUTIONS SECTION ===== -->
    <section id="soluciones" class="solutions-section">
        <div class="solutions-container">
            <div class="section-header">
                <h2>Nuestras Soluciones</h2>
                <p>Servicios especializados para cada tipo de vehículo</p>
            </div>

            <div class="solutions-grid">
                <div class="solution-card">
                    <div class="solution-icon">🚗</div>
                    <h3>Vehículos Particulares</h3>
                    <p>Mantenimiento, reparación y diagnóstico para autos de pasajeros con garantía completa.</p>
                    <ul class="solution-features">
                        <li>✓ Cambio de aceite y filtros</li>
                        <li>✓ Reparación de frenos</li>
                        <li>✓ Diagnóstico electrónico</li>
                    </ul>
                </div>

                <div class="solution-card">
                    <div class="solution-icon">🚙</div>
                    <h3>Flotillas Empresariales</h3>
                    <p>Mantenimiento preventivo programado para empresas con múltiples vehículos.</p>
                    <ul class="solution-features">
                        <li>✓ Planes customizados</li>
                        <li>✓ Facturación centralizada</li>
                        <li>✓ Reportes de mantenimiento</li>
                    </ul>
                </div>

                <div class="solution-card">
                    <div class="solution-icon">⚡</div>
                    <h3>Sistemas Electrónicos</h3>
                    <p>Diagnóstico y reparación especializada de sistemas eléctricos y computarizados.</p>
                    <ul class="solution-features">
                        <li>✓ Diagnóstico OBD</li>
                        <li>✓ Reprogramación</li>
                        <li>✓ Sistemas híbridos</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== BENEFITS / STATS ===== -->
    <section class="benefits-stats">
        <div class="benefits-container">
            <div class="stat-box">
                <h3 class="stat-number">15+</h3>
                <p>Años de Experiencia</p>
            </div>
            <div class="stat-box">
                <h3 class="stat-number">5000+</h3>
                <p>Reparaciones Realizadas</p>
            </div>
            <div class="stat-box">
                <h3 class="stat-number">98%</h3>
                <p>Satisfacción Garantizada</p>
            </div>
            <div class="stat-box">
                <h3 class="stat-number">24/7</h3>
                <p>Disponibilidad</p>
            </div>
        </div>
    </section>

    <!-- ===== CUSTOMER STORIES ===== -->
    <section id="clientes" class="customer-stories">
        <div class="stories-container">
            <div class="section-header">
                <h2>Historias de Nuestros Clientes</h2>
                <p>Lee qué dicen quienes confían en TECNIMECANICA CALIFORNIA</p>
            </div>

            <div class="stories-grid">
                <div class="story-card">
                    <div class="story-quote">
                        <p>"Excelente servicio, técnicos muy profesionales. Mi vehículo quedó como nuevo. Sin duda
                            volveré a confiar en ustedes."</p>
                    </div>
                    <div class="story-author">
                        <div class="author-info">
                            <h4>Carlos Mendoza</h4>
                            <p>Dueño, Taxi Ejecutivo</p>
                        </div>
                    </div>
                    <div class="story-rating">★★★★★ 5.0</div>
                </div>

                <div class="story-card">
                    <div class="story-quote">
                        <p>"Servicio rápido, eficiente y honesto. El personal es transparente en sus diagnósticos. Muy
                            recomendado para cualquiera que cuide su inversión."</p>
                    </div>
                    <div class="story-author">
                        <div class="author-info">
                            <h4>María González</h4>
                            <p>Gerente, Flota Empresarial</p>
                        </div>
                    </div>
                    <div class="story-rating">★★★★★ 5.0</div>
                </div>

                <div class="story-card">
                    <div class="story-quote">
                        <p>"Llevo 5 años trayendo mis vehículos aquí. Buen equipamiento, técnicos profesionales, precios
                            justos. Confío plenamente en TECNIMECANICA CALIFORNIA."</p>
                    </div>
                    <div class="story-author">
                        <div class="author-info">
                            <h4>Roberto Sánchez</h4>
                            <p>Propietario, Concesionario</p>
                        </div>
                    </div>
                    <div class="story-rating">★★★★★ 5.0</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ABOUT ===== -->
    <section class="about">
        <div class="services-container">
            <div class="section-title">
                <h2>Conócenos</h2>
                <p>TECNIMECANICA CALIFORNIA es innovación y experiencia para el mantenimiento de tu vehículo</p>
            </div>

            <div class="about-content">
                <p>Ofrecemos reparaciones automotrices garantizadas, respaldado por más de 15 años de experiencia y un
                    compromiso constante con la innovación. Mantenemos tu vehículo en las mejores condiciones con
                    herramientas y equipos de alta tecnología.</p>

                <p>Nuestro personal constantemente capacitado se especializa en el mantenimiento, diagnóstico y
                    corrección de fallas en automóviles modernos. Contamos con un equipo multidisciplinario que puede
                    atender desde vehículos particulares hasta flotillas empresariales.</p>

                <p>En TECNIMECANICA CALIFORNIA, tu confianza es nuestra prioridad. Ofrecemos transparencia total,
                    precios justos y garantía en todos nuestros servicios. ¡Somos el taller que necesitas!</p>
            </div>
        </div>
    </section>

    <!-- ===== CTA FINAL ===== -->
    <section id="contacto" class="cta-final">
        <div class="cta-container">
            <h2>¿Listo para reparar tu vehículo?</h2>
            <p>Agenda una cita hoy y recibe diagnóstico gratuito</p>
            <div class="cta-buttons">
                <a href="#citas" onclick="checkAuth(event, 'citas')" class="btn-primary-lg">Agendar Ahora</a>
                <a href="tel:+56912345678" class="btn-secondary-lg">Llamar: +56 9 1234 5678</a>
            </div>
        </div>
    </section>

    <!-- ===== APPOINTMENT SECTION (NO AUTH) ===== -->
    <section id="citas" class="booking-section" style="display: none;">
        <div class="auth-required">
            <div class="auth-message">
                <h3>Acceso Restringido</h3>
                <p>Para agendar una cita, debes registrarte o iniciar sesión</p>
                <button class="btn btn-primary" onclick="toggleAuthModal()">Registrarse / Ingresar</button>
            </div>
        </div>
    </section>

    <!-- ===== APPOINTMENT SECTION (AUTH) ===== -->
    <section id="citas-auth" class="booking-section">
        <div class="booking-container">
            <div class="section-title">
                <h2>Agendar una Cita</h2>
                <p>Reserva tu horario con nuestros técnicos especializados</p>
            </div>

            <form class="booking-form" onsubmit="submitBooking(event)">
                <div class="form-row">
                    <input type="text" placeholder="Nombre Completo" required>
                    <input type="email" placeholder="Correo Electrónico" required>
                </div>
                <div class="form-row">
                    <input type="tel" placeholder="Teléfono" required>
                    <input type="text" placeholder="Modelo del Vehículo" required>
                </div>
                <div class="form-row">
                    <input type="date" required>
                    <select required>
                        <option value="">Selecciona una hora</option>
                        <option value="09:00">09:00 - 10:00</option>
                        <option value="10:00">10:00 - 11:00</option>
                        <option value="11:00">11:00 - 12:00</option>
                        <option value="14:00">14:00 - 15:00</option>
                        <option value="15:00">15:00 - 16:00</option>
                        <option value="16:00">16:00 - 17:00</option>
                    </select>
                </div>
                <textarea placeholder="Describe el problema o servicio requerido" rows="4" required></textarea>
                <button type="submit" class="btn btn-primary">Agendar Cita</button>
            </form>
        </div>
    </section>

    <!-- ===== TRACKING SECTION (NO AUTH) ===== -->
    <section id="seguimiento" class="tracking-section" style="display: none;">
        <div class="auth-required">
            <div class="auth-message">
                <h3>Acceso Restringido</h3>
                <p>Para ver el progreso de tu reparación, debes registrarte o iniciar sesión</p>
                <button class="btn btn-primary" onclick="toggleAuthModal()">Registrarse / Ingresar</button>
            </div>
        </div>
    </section>

    <!-- ===== TRACKING SECTION (AUTH) ===== -->
    <section id="seguimiento-auth" class="tracking-section">
        <div class="tracking-container">
            <div class="section-title">
                <h2>Seguimiento de tu Reparación</h2>
                <p>Ingresa el código de seguimiento para ver el progreso de tu vehículo</p>
            </div>

            <div class="tracking-wrapper">
                <div class="tracking-form">
                    <input type="text" id="trackingCode" placeholder="Código de Seguimiento" required>
                    <button type="button" class="btn btn-primary" onclick="searchTracking()">Buscar</button>
                </div>

                <div id="trackingResult" class="tracking-result" style="display: none;">
                    <div class="tracking-header">
                        <h3>Toyota Corolla 2020</h3>
                        <p>Placa: ABC-123</p>
                    </div>

                    <div class="tracking-timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-marker">✓</div>
                            <div class="timeline-content">
                                <h4>Recepción del Vehículo</h4>
                                <p>2026-01-19 08:30 - Vehículo recibido</p>
                            </div>
                        </div>

                        <div class="timeline-item completed">
                            <div class="timeline-marker">✓</div>
                            <div class="timeline-content">
                                <h4>Diagnóstico Inicial</h4>
                                <p>2026-01-19 10:00 - Diagnóstico completado</p>
                            </div>
                        </div>

                        <div class="timeline-item active">
                            <div class="timeline-marker">⚙️</div>
                            <div class="timeline-content">
                                <h4>En Reparación</h4>
                                <p>2026-01-19 14:00 - En proceso</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">📋</div>
                            <div class="timeline-content">
                                <h4>Prueba de Funcionamiento</h4>
                                <p>Pendiente - Verificación</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">✨</div>
                            <div class="timeline-content">
                                <h4>Entrega Final</h4>
                                <p>Pendiente - Entrega</p>
                            </div>
                        </div>
                    </div>

                    <div class="tracking-details">
                        <div class="detail-box">
                            <h4>Servicios Realizados</h4>
                            <ul>
                                <li>Cambio de aceite y filtro</li>
                                <li>Revisión de frenos</li>
                                <li>Diagnosis electrónica</li>
                            </ul>
                        </div>
                        <div class="detail-box">
                            <h4>Información de Contacto</h4>
                            <p><strong>Técnico:</strong> Carlos Mendoza</p>
                            <p>📞 +56 9 1234 5678</p>
                            <p>📧 contacto@tecnimecanica-california.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer>
        <div class="footer-content">
            <div>
                <h4>Sobre TECNIMECANICA</h4>
                <ul>
                    <li><a href="#">Quiénes somos</a></li>
                    <li><a href="#">Nuestro equipo</a></li>
                    <li><a href="#">Certificaciones</a></li>
                    <li><a href="#">Ubicación</a></li>
                </ul>
            </div>

            <div>
                <h4>Servicios</h4>
                <ul>
                    <li><a href="#">Mantenimiento</a></li>
                    <li><a href="#">Reparaciones</a></li>
                    <li><a href="#">Diagnóstico</a></li>
                    <li><a href="#">Electrónica</a></li>
                </ul>
            </div>

            <div>
                <h4>Contacto</h4>
                <ul>
                    <li><a href="tel:+56912345678">+56 9 1234 5678</a></li>
                    <li><a href="mailto:info@tecnimecanica.cl">info@tecnimecanica.cl</a></li>
                    <li>Lun-Vie: 8:00 - 18:00</li>
                    <li>Sab: 9:00 - 14:00</li>
                </ul>
            </div>

            <div>
                <h4>Redes Sociales</h4>
                <div class="social-links">
                    <a href="FACEBOOK_URL" class="social-link" title="Facebook" target="_blank">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="INSTAGRAM_URL" class="social-link" title="Instagram" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="TIKTOK_URL" class="social-link" title="TikTok" target="_blank">
                        <i class="fab fa-tiktok"></i>
                    </a>
                </div>
                <p style="font-size: 0.85rem; color: #9ca3af; margin-top: 1rem;">Síguenos en nuestras redes</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; 2026 TECNIMECANICA CALIFORNIA - Servicios Automotrices. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- ===== FLOATING SOCIAL LINKS ===== -->
    <div class="floating-social">
        <a href="FACEBOOK_URL" class="float-social-btn facebook" title="Facebook" target="_blank">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="INSTAGRAM_URL" class="float-social-btn instagram" title="Instagram" target="_blank">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="TIKTOK_URL" class="float-social-btn tiktok" title="TikTok" target="_blank">
            <i class="fab fa-tiktok"></i>
        </a>
    </div>

    <!-- ===== AUTH MODAL ===== -->
    <div id="authModal" class="auth-modal">
        <div class="auth-modal-content">
            <button class="close-modal" onclick="toggleAuthModal()">✕</button>

            <div class="auth-tabs">
                <button class="auth-tab active" onclick="switchAuthTab(event, 'login')">Ingresar</button>
                <button class="auth-tab" onclick="switchAuthTab(event, 'register')">Registrarse</button>
            </div>

            <!-- Login -->
            <div id="login-tab" class="auth-tab-content active">
                <h2>Ingresar a tu Cuenta</h2>
                <form onsubmit="submitLogin(event)">
                    <input type="email" placeholder="Correo Electrónico" required>
                    <input type="password" placeholder="Contraseña" required>
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                </form>
                <p class="auth-footer">¿Olvidaste tu contraseña? <a href="#">Recupérala aquí</a></p>
            </div>

            <!-- Register -->
            <div id="register-tab" class="auth-tab-content">
                <h2>Crear Nueva Cuenta</h2>
                <form onsubmit="submitRegister(event)">
                    <input type="text" placeholder="Nombre Completo" required>
                    <input type="email" placeholder="Correo Electrónico" required>
                    <input type="tel" placeholder="Teléfono" required>
                    <input type="password" placeholder="Contraseña" required>
                    <input type="password" placeholder="Confirmar Contraseña" required>
                    <div class="checkbox">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">Acepto los términos y condiciones</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Registrarse</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        let isAuthenticated = false;
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-item');
        const indicators = document.querySelectorAll('.indicator');

        // Auth Modal
        function toggleAuthModal() {
            const modal = document.getElementById('authModal');
            modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
        }

        function switchAuthTab(event, tab) {
            event.preventDefault();
            document.querySelectorAll('.auth-tab').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.auth-tab-content').forEach(content => content.classList.remove('active'));
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }

        function submitLogin(event) {
            event.preventDefault();
            isAuthenticated = true;
            toggleAuthModal();
            showAuthenticatedSections();
            alert('¡Bienvenido! Sesión iniciada correctamente');
        }

        function submitRegister(event) {
            event.preventDefault();
            isAuthenticated = true;
            toggleAuthModal();
            showAuthenticatedSections();
            alert('¡Bienvenido! Cuenta creada correctamente');
        }

        function showAuthenticatedSections() {
            document.getElementById('citas').style.display = 'none';
            document.getElementById('seguimiento').style.display = 'none';
            document.getElementById('citas-auth').style.display = 'block';
            document.getElementById('seguimiento-auth').style.display = 'block';
        }

        function checkAuth(event, section) {
            if (!isAuthenticated) {
                event.preventDefault();
                toggleAuthModal();
            }
        }

        function submitBooking(event) {
            event.preventDefault();
            alert('¡Cita agendada correctamente!');
            event.target.reset();
        }

        // Carousel
        function showSlide(n) {
            if (n >= slides.length) currentSlide = 0;
            if (n < 0) currentSlide = slides.length - 1;
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(ind => ind.classList.remove('active'));
            slides[currentSlide].classList.add('active');
            indicators[currentSlide].classList.add('active');
        }

        function changeSlide(n) {
            currentSlide += n;
            showSlide(currentSlide);
        }

        function currentSlideFunc(n) {
            currentSlide = n;
            showSlide(currentSlide);
        }

        // Tracking
        function searchTracking() {
            const code = document.getElementById('trackingCode').value;
            if (code.trim() !== '') {
                document.getElementById('trackingResult').style.display = 'block';
            }
        }

        // Close modal
        window.onclick = function (event) {
            const modal = document.getElementById('authModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        const videos = document.querySelectorAll('.video-player');

        videos.forEach(video => {
            video.addEventListener('play', () => {
                // Cuando este video inicie, pausamos los demás
                videos.forEach(otherVideo => {
                    if (otherVideo !== video) {
                        otherVideo.pause();
                    }
                });
            });
        });

        // Auto carousel
        setInterval(() => changeSlide(1), 5000);
    </script>
</body>

</html>