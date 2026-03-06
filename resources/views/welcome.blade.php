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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/landing.js'])
    @endif

    <style>
        /* Modern Form Aesthetics */
        .booking-modal-overlay {
            backdrop-filter: blur(8px);
            background-color: rgba(0, 0, 0, 0.6);
        }

        .booking-modal-container {
            border-radius: 24px !important;
            overflow-y: auto;
            overflow-x: hidden;
            max-height: 95vh;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        /* Custom Scrollbar for Webkit */
        .booking-modal-container::-webkit-scrollbar {
            width: 8px;
        }

        .booking-modal-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .booking-modal-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }

        .booking-form-premium input,
        .booking-form-premium select,
        .booking-form-premium textarea,
        .premium-select {
            border: 2px solid #f3f4f6;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: #f9fafb;
            color: #1f2937;
            width: 100%;
        }

        .booking-form-premium input:focus,
        .booking-form-premium select:focus,
        .booking-form-premium textarea:focus,
        .premium-select:focus {
            border-color: #2563eb;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .booking-form-premium input::placeholder {
            color: #9ca3af;
        }

        .booking-form-premium label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            font-weight: 700;
            margin-bottom: 8px;
            display: block;
        }

        /* Time Slot Modernization */
        .time-slot {
            border-radius: 12px !important;
            border: 2px solid #f3f4f6 !important;
            font-weight: 600 !important;
            color: #4b5563;
        }

        .time-slot:hover {
            border-color: #bfdbfe !important;
            background-color: #eff6ff !important;
            color: #1e40af !important;
        }

        .time-slot.selected {
            background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
            border-color: transparent !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
            transform: translateY(-1px);
        }

        /* Buttons */
        .btn-confirm {
            background: linear-gradient(135deg, #111827, #000000);
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: transform 0.2s;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Ajustes para los controles de usuario autenticado */
        .navbar-nav .user-action-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar-nav .user-greeting {
            font-size: 0.85rem;
            font-weight: 600;
            color: #111;
            white-space: nowrap;
        }

        .navbar-nav .user-action-pill {
            display: inline-flex;
            border: 1px solid rgba(148, 163, 184, 0.5);
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 999px;
            overflow: hidden;
        }

        .navbar-nav .user-action-pill a,
        .navbar-nav .user-action-pill button {
            padding: 0.25rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            transition: background-color 0.2s;
            text-decoration: none;
        }

        .navbar-nav .user-action-pill .pill-app {
            background-color: #2563eb;
            color: #fff;
        }

        .navbar-nav .user-action-pill .pill-app:hover {
            background-color: #1d4ed8;
        }

        .navbar-nav .user-action-pill .pill-logout {
            background-color: #dc2626;
            color: #fff;
        }

        .navbar-nav .user-action-pill .pill-logout:hover {
            background-color: #b91c1c;
        }

        .navbar-nav .user-action-pill .pill-separator {
            width: 1px;
            background-color: rgba(255, 255, 255, 0.4);
            margin: 0 0.15rem;
        }
    </style>
</head>

<body>
    <!-- ===== NAVBAR ===== -->
    <nav class="navbar">
        <div class="navbar-inner">
            <div class="navbar-logo">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/BMW.svg/2048px-BMW.svg.png"
                    alt="BMW Logo" style="height: 45px; margin-right: 12px;">
                <span style="font-style: italic;">BMW <span style="color: var(--m-red);">/</span><span
                        style="color: var(--m-blue-dark);">/</span><span style="color: var(--m-blue-light);">/</span>M
                    SERVICE</span>
            </div>

            <div class="navbar-menu">
                <a href="#servicios" class="navbar-link">Servicios</a>
                <a href="#galeria" class="navbar-link">Nuestros éxitos</a>
                <a href="#soluciones" class="navbar-link">Soluciones</a>
                <a href="#clientes" class="navbar-link">Clientes</a>
                <a href="#contacto" class="navbar-link">Contacto</a>
            </div>

            <div class="navbar-nav">
                @auth
                    <div class="user-action-wrapper">
                        <span class="user-greeting">Hola, {{ auth()->user()->name }}</span>
                        <div class="user-action-pill">
                            <a href="{{ route('panel.dashboard') }}" class="pill-app" title="Ir a la aplicación">
                                <i class="fas fa-arrow-right-to-bracket"></i>
                                <span>App</span>
                            </a>
                            <div class="pill-separator" aria-hidden="true"></div>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="pill-logout">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Salir</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Ingresar</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- ===== HERO ===== -->
    <section class="hero">
        <div class="hero-inner">
            <h1 style="text-shadow: 0 4px 10px rgba(0,0,0,0.5);">THE ULTIMATE DRIVING MACHINE CARE</h1>
            <p class="hero-subtitle" style="text-shadow: 0 2px 5px rgba(0,0,0,0.5);">Mantenimiento certificado para su
                BMW con estándares de fábrica Munich.
                <br>Especialistas en Serie M, X y modelos i.
            </p>
            <div class="hero-buttons">
                <a href="#soluciones" class="btn btn-primary">Explorar Servicios</a>
                <a href="#contacto" class="btn btn-secondary">Cotizar Servicio</a>
            </div>
        </div>
    </section>

    <section class="trust">
        <p>Certificación BMW Service Partner | <strong>Uso exclusivo de recambios originales</strong> | Garantía Oficial
        </p>
    </section>


    <section id="servicios" class="services">
        <div class="services-inner">
            <div class="section-title">
                <h2>Experiencia de Servicio BMW</h2>
                <p>Mantenimiento de clase mundial para su vehículo</p>
            </div>

            <div class="services-grid-premium" id="services-container">
                <!-- Las imágenes de servicios se cargarán dinámicamente desde la API -->
                <div class="loading-spinner">
                    <p>Cargando servicios...</p>
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

            <div class="gallery-carousel-wrapper">
                <button class="carousel-nav-btn prev" id="gallery-prev">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <div class="gallery-viewport">
                    <div class="gallery-track" id="gallery-container">
                        <!-- Las imágenes de galería se cargarán dinámicamente -->
                        <div class="loading-spinner">
                            <p>Cargando galería...</p>
                        </div>
                    </div>
                </div>

                <button class="carousel-nav-btn next" id="gallery-next">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </section>

    <section id="videos" class="videos-section">
        <div class="videos-container">
            <div class="section-title text-center">
                <h2 style="color: #111; margin-bottom: 3rem;">Más historias de éxito</h2>
            </div>
            <div class="videos-grid-modern" id="videos-container">
                <!-- Los videos se cargarán dinámicamente desde landing.js -->
                <div class="loading-spinner">
                    <p>Cargando historias...</p>
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
                <h2>Soluciones Integrales</h2>
                <p>Tecnología y experiencia para cada necesidad</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="feature-text">
                        <h4>Garantía Extendida</h4>
                        <p>Todos nuestros trabajos incluyen garantía por escrito.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-car-crash"></i></div>
                    <div class="feature-text">
                        <h4>Siniestros & Carrocería</h4>
                        <p>Trabajamos con las principales aseguradoras.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-laptop-code"></i></div>
                    <div class="feature-text">
                        <h4>Coding & Retrofit</h4>
                        <p>Activación de funciones ocultas y upgrades de equipamiento.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-key"></i></div>
                    <div class="feature-text">
                        <h4>Cerrajería Electrónica</h4>
                        <p>Programación de llaves perdidas y módulos FEM/BDC.</p>
                    </div>
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
    <section class="about-modern">
        <div class="about-grid">
            <div class="about-image">
                <img src="https://img.freepik.com/foto-gratis/reparacion-coches-mantenimiento-reparacion-neumaticos-coche-garaje-mecanico_23-2148154673.jpg"
                    alt="Taller BMW">
            </div>
            <div class="about-text">
                <h2>Pasión por la Ingeniería Alemana</h2>
                <p class="highlight-text">"No solo reparamos autos, restauramos la experiencia de conducción original."
                </p>
                <p>En TECNIMECANICA CALIFORNIA, fusionamos la artesanía tradicional con la última tecnología de
                    diagnóstico. Con más de 15 años liderando el mantenimiento de alta gama, entendemos que un BMW no es
                    solo un medio de transporte, es una declaración de principios.</p>

                <div class="stats-mini">
                    <div><span>15+</span> Años</div>
                    <div><span>5k+</span> Autos</div>
                    <div><span>100%</span> Certificado</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== CTA FINAL ===== -->




    <!-- ===== TRACKING SECTION (AUTH) ===== -->
    <section id="seguimiento-auth" class="tracking-section-premium">
        <div class="tracking-container">
            <div class="section-title">
                <h2>BMW Service Geniuses</h2>
                <p>Monitoreo en tiempo real de su unidad</p>
            </div>

            <div class="tracking-wrapper">
                <!-- Search Box -->
                <div class="tracking-search-box">
                    <input type="text" id="trackingCode" placeholder="Ingrese VIN o Orden de Servicio" required>
                    <button type="button" class="btn btn-primary" onclick="searchTracking()">
                        <i class="fas fa-search"></i> Rastrear
                    </button>
                </div>

                <!-- Result Dashboard (Hidden by default) -->
                <div id="trackingResult" class="tracking-dashboard" style="display: none;">

                    <!-- Vehicle Header -->
                    <div class="dashboard-header">
                        <div class="vehicle-info">
                            <span class="status-badge pulse">En Proceso</span>
                            <h3>BMW 330i M Sport</h3>
                            <p class="vin">VIN: WBA5R...8291</p>
                        </div>
                        <div class="estimated-time">
                            <small>Entrega Estimada</small>
                            <strong>Mañana, 17:00 PM</strong>
                        </div>
                    </div>

                    <!-- Progress Stepper -->
                    <div class="status-stepper">
                        <div class="step completed">
                            <div class="step-icon"><i class="fas fa-check"></i></div>
                            <p>Recepción</p>
                        </div>
                        <div class="step completed">
                            <div class="step-icon"><i class="fas fa-laptop-medical"></i></div>
                            <p>Diagnóstico</p>
                        </div>
                        <div class="step active">
                            <div class="step-icon"><i class="fas fa-tools"></i></div>
                            <p>Servicio</p>
                            <span class="step-time">En progreso</span>
                        </div>
                        <div class="step">
                            <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                            <p>Control Calidad</p>
                        </div>
                        <div class="step">
                            <div class="step-icon"><i class="fas fa-flag-checkered"></i></div>
                            <p>Listo</p>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="dashboard-details">
                        <div class="detail-card">
                            <h4><i class="fas fa-clipboard-list"></i> Actividades Completadas</h4>
                            <ul class="activity-list">
                                <li><span>✓</span> Cambio de Aceite 5W-30</li>
                                <li><span>✓</span> Inspección Multi-puntos</li>
                                <li><span>✓</span> Escaneo ISTA/D</li>
                            </ul>
                        </div>
                        <div class="detail-card">
                            <h4><i class="fas fa-user-friends"></i> Asesor de Servicio</h4>
                            <div class="advisor-info">
                                <div class="advisor-avatar">CM</div>
                                <div>
                                    <strong>Carlos Mendoza</strong>
                                    <p>Certificado BMW Nivel 3</p>
                                    <a href="tel:+50233970345" class="link-text">Contactar</a>
                                </div>
                            </div>
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
                    <li><a href="tel:+50233970345">502 33970345</a></li>
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



    <!-- Auth Modal Removed: Using dedicated Login Page -->

    <!-- ===== FLOATING ACTION BUTTONS (FAB) ===== -->
    <div class="fab-container">
        <!-- WhatsApp Button -->
        <a href="javascript:void(0)" onclick="openWhatsAppModal()" class="fab-btn whatsapp">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
        </a>

        <!-- Booking Button -->
        <button class="fab-btn booking" onclick="openBookingModal()">
            <i class="fas fa-calendar-alt"></i>
            <span>Agendar Cita</span>
        </button>
    </div>

    <!-- ===== BOOKING MODAL ===== -->
    <!-- ===== BOOKING MODAL (PREMIUM WIDE) ===== -->
    <div id="bookingModal" class="booking-modal-overlay">
        <div class="booking-modal-container">
            <button class="close-modal-btn" onclick="toggleBookingModal()">✕</button>

            <div class="booking-split-layout">
                <!-- Left: Contact & Details -->
                <div class="booking-left-panel">
                    <div class="panel-header">
                        <h2>Agendar Servicio</h2>
                        <p>BMW Service Inclusive</p>
                    </div>

                    <form class="booking-form-premium" id="bookingForm" onsubmit="submitBooking(event)" novalidate>

                        <div
                            style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                            <label
                                style="color: #1e40af; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; display: block;">¿Ya
                                eres cliente?</label>
                            <p style="font-size: 0.85rem; color: #3b82f6; margin-bottom: 10px;">Ingresa tu correo o
                                teléfono para autocompletar tus datos y ver tus vehículos.</p>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="lookupInput" placeholder="Correo o Teléfono"
                                    style="flex: 1; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px; font-size: 0.9rem;">
                                <button type="button" onclick="lookupClient()"
                                    style="background-color: #2563eb; color: white; border: none; border-radius: 8px; padding: 0 15px; font-weight: 600; cursor: pointer;">Buscar</button>
                            </div>
                        </div>

                        <div class="form-group-premium">
                            <label>Información de Contacto</label>
                            <input type="text" id="clientName" placeholder="Nombre Completo / Empresa" required>
                            <input type="email" id="clientEmail" placeholder="Correo Electrónico" required>
                            <input type="tel" id="clientPhone" placeholder="Teléfono Móvil" required>
                        </div>

                        <div class="form-group-premium">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <label style="margin-bottom: 0;">Datos del Vehículo</label>
                            </div>

                            <!-- Contenedor para mostrar vehículos existentes (oculto por defecto) -->
                            <div id="existingVehiclesContainer"
                                style="display: none; margin-bottom: 15px; background: #fdfdfd; border: 1px dashed #cbd5e1; padding: 10px; border-radius: 8px;">
                                <label style="font-size: 0.8rem; color: #475569;">Selecciona un vehículo registrado o
                                    ignora esto para registrar uno nuevo:</label>
                                <select id="existingVehiclesSelect" class="premium-select"
                                    style="margin-top: 5px; border-color: #cbd5e1; background-color: #fff;"
                                    onchange="selectExistingVehicle()">
                                    <option value="">-- Ignorar / Registrar nuevo vehículo --</option>
                                </select>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <input type="text" id="vehiculoPlaca" placeholder="Placa (Indispensable)" required
                                    style="text-transform: uppercase;">
                                <div class="select-wrapper">
                                    <select id="vehiculoMarcaSelect" class="premium-select">
                                        <option value="">Cargando marcas...</option>
                                    </select>
                                    <input type="text" id="vehiculoMarca" placeholder="Escriba Marca..."
                                        class="hidden mt-2">
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
                                <div class="select-wrapper">
                                    <select id="vehiculoModeloSelect" class="premium-select" disabled>
                                        <option value="">Seleccione Marca...</option>
                                    </select>
                                    <input type="text" id="vehiculoModelo" placeholder="Escriba Modelo..."
                                        class="hidden mt-2">
                                </div>
                                <div class="select-wrapper">
                                    <select id="vehiculoVersionSelect" class="premium-select" disabled>
                                        <option value="">Seleccione Modelo...</option>
                                    </select>
                                    <input type="text" id="vehiculoVersion" placeholder="Escriba Versión..."
                                        class="hidden mt-2">
                                </div>
                            </div>
                        </div>

                        <div class="form-group-premium">
                            <label>Tipo de Requerimiento</label>
                            <textarea id="requestDetails"
                                placeholder="Describa el servicio (ej. Mantención 40.000km, Testigo encendido...)"
                                rows="3" required></textarea>
                        </div>



                        <!-- Hidden Inputs for Date/Time -->

                        <input type="hidden" id="selectedTime">

                        <button type="submit" class="btn btn-primary submit-btn"
                            style="width: 100%; text-transform: uppercase; font-weight: 800; letter-spacing: 1px; padding: 18px;">AGENDAR
                            CITA</button>
                    </form>
                </div>

                <!-- Right: Calendar & Time -->
                <div class="booking-right-panel">
                    <div class="panel-header-right">
                        <h3>Selecciona Fecha y Hora</h3>
                        <p>Disponibilidad en tiempo real</p>
                    </div>

                    <div class="calendar-wrapper" style="margin-bottom: 20px;">
                        <label>Taller / Sucursal de Preferencia</label>
                        <div class="select-wrapper">
                            <select id="sucursalSelect" class="premium-select">
                                <option value="">Seleccione Taller / Sucursal...</option>
                            </select>
                        </div>
                    </div>

                    <div class="calendar-wrapper">
                        <label>Fecha Preferida</label>
                        <input type="date" id="selectedDate" class="premium-date-input"
                            onchange="updateTimeSlots(this.value)">
                    </div>

                    <div class="time-selection-wrapper">
                        <label>Horarios Disponibles</label>
                        <div class="time-grid" id="timeSlotsGrid">
                            <!-- Times generated by JS -->
                            <button type="button" class="time-slot" onclick="selectTime(this, '07:00')">07:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '08:00')">08:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '09:00')">09:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '10:00')">10:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '11:00')">11:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '12:00')">12:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '13:00')">13:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '14:00')">14:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '15:00')">15:00</button>
                            <button type="button" class="time-slot" onclick="selectTime(this, '16:00')">16:00</button>
                        </div>
                        <p class="helper-text">* Horarios sujetos a confirmación por el asesor.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Selección de Sucursal WhatsApp -->
    <div id="whatsappModal" class="booking-modal-overlay">
        <div class="booking-modal-panel"
            style="max-width: 400px; background: #ffffff; border-radius: 24px; padding: 10px; box-shadow: 0 20px 50px rgba(0,0,0,0.4);">
            <div class="booking-header" style="border-bottom: 1px solid #f3f4f6; margin-bottom: 0;">
                <h3 style="color: #111827; font-weight: 800; font-size: 1.25rem;">¿Deseas comunicarte con nosotros?</h3>
                <button type="button" class="close-booking" onclick="closeWhatsAppModal()"
                    style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">&times;</button>
            </div>
            <div class="booking-body" style="padding: 25px 20px;">
                <p style="margin-bottom: 25px; color: #4b5563; font-size: 0.95rem; font-weight: 500; line-height: 1.5;">
                    Por favor, selecciona una sucursal para que un asesor te brinde atención personalizada vía WhatsApp.
                </p>
                <div id="whatsappBranchesList" class="whatsapp-branches-grid">
                    <!-- Cargado por JS -->
                    <div class="loading-dots" style="color: #2563eb; font-weight: 700;">Localizando talleres cercanos...
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>