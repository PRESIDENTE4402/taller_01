<!DOCTYPE html>
<html lang="es" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-sucursal-id" content="{{ Auth::user()->sucursales->first()?->id }}">
    <title>{{ config('app.name', 'TallerPro') }} - Gestión</title>

    {{-- Tipografía Oficial Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Iconos FontAwesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    {{-- Estilos Core (CDN para funcionamiento inmediato) --}}
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }

        /* Fondo gris suave */

        /* Sidebar personalizado oscuro profesional */
        .sidebar-container {
            background-color: #1e293b;
            /* Slate 800 */
            color: #cbd5e1;
        }

        /* Item activo en el sidebar con efecto glow sutil */
        .sidebar-active {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-right: 3px solid #60a5fa;
        }

        .sidebar-item:hover:not(.sidebar-active) {
            background-color: rgba(255, 255, 255, 0.05);
            color: #f1f5f9;
        }

        /* Scrollbar elegante para el menú */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-scroll::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        /* Funcionalidad de Colapso del menú lateral */
        .sidebar-collapsed {
            width: 5rem !important;
        }

        /* Desactivar el clip de scroll para que escapen los tooltips al colapsar */
        .sidebar-collapsed .sidebar-scroll {
            overflow: visible !important;
        }

        .sidebar-collapsed .menu-text,
        .sidebar-collapsed .logo-text,
        .sidebar-collapsed .section-title,
        .sidebar-collapsed .user-info {
            display: none !important;
        }

        .sidebar-collapsed .sidebar-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar-collapsed .sidebar-active {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            border-right: none;
            border-left: 3px solid #60a5fa;
            /* Cambiado a la izquierda para centrar mejor visualmente */
        }

        /* Tooltips personalizados habilitados solo al colapsar (DaisyUI / Tailwind pseudo classes override) */
        .sidebar-menu-btn {
            position: relative;
        }

        body:not(.sidebar-collapsed-body) .sidebar-menu-btn:hover::before,
        body:not(.sidebar-collapsed-body) .sidebar-menu-btn:hover::after,
        body:not(.sidebar-collapsed-body) .sidebar-menu-btn::before,
        body:not(.sidebar-collapsed-body) .sidebar-menu-btn::after {
            display: none !important;
        }
    </style>
</head>

<body class="h-screen overflow-hidden flex text-gray-800 bg-gray-50 sidebar-body-wrapper transition-colors">

    {{-- MOBILE BACKDROP --}}
    <div id="mobile-backdrop"
        class="fixed inset-0 bg-black/50 z-10 hidden md:hidden glass transition-opacity duration-300"
        onclick="toggleMobileMenu()"></div>

    {{-- SIDEBAR / MENÚ LATERAL OSCURO --}}
    <aside id="sidebar"
        class="w-64 sidebar-container shadow-xl flex-shrink-0 flex flex-col transition-all duration-300 fixed md:relative z-20 h-full -translate-x-full md:translate-x-0">

        {{-- Logo Area --}}
        <div class="h-16 flex items-center px-6 border-b border-slate-700 bg-slate-900 justify-between">
            <div class="flex items-center gap-3 font-bold text-xl tracking-tight text-white overflow-hidden whitespace-nowrap">
                <div
                    class="w-8 h-8 rounded bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg flex-shrink-0">
                    <i class="fas fa-wrench text-xs text-white"></i>
                </div>
                <span class="logo-text">Taller<span class="text-blue-400">Pro</span></span>
            </div>
            <!-- Close Button Mobile -->
            <button class="md:hidden text-gray-400 hover:text-white" onclick="toggleMobileMenu()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Scrollable Menu --}}
        <div class="flex-1 overflow-y-auto py-4 sidebar-scroll">
            <nav class="space-y-1 px-3">

                {{-- Sección Principal --}}
                <div class="px-3 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider section-title whitespace-nowrap overflow-hidden">Principal</div>

                @can('ver_dashboard')
                <a href="{{ route('panel.dashboard') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.dashboard') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Dashboard">
                    <i class="fas fa-chart-pie w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Dashboard</span>
                </a>
                @endcan

                <a href="{{ route('panel.clientes.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.clientes.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Directorio Clientes">
                    <i class="fas fa-address-book w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Directorio Clientes</span>
                </a>

                <a href="{{ route('panel.vehiculos.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.vehiculos.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Directorio Vehículos">
                    <i class="fas fa-car w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Directorio Vehículos</span>
                </a>

                {{-- Sección Operaciones --}}
                @if(Gate::check('gestionar_citas') || Gate::check('gestionar_recepcion') || Gate::check('gestionar_ordenes_trabajo') || auth()->user()->hasRole('mecanico') || auth()->user()->hasRole('ayudante') || auth()->user()->hasRole('tecnico'))
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider section-title whitespace-nowrap overflow-hidden">Operaciones</div>

                @can('gestionar_citas')
                <a href="{{ route('panel.operaciones.citas.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.operaciones.citas.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Gestionar Citas">
                    <i class="fas fa-calendar-check w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Gestionar Citas</span>
                </a>
                @endcan

                @can('gestionar_recepcion')
                <a href="{{ route('panel.operaciones.ordenes_trabajo.dashboard') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.operaciones.ordenes_trabajo.dashboard') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Recepción Vehículos">
                    <i class="fas fa-car w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Recepción Vehículos</span>
                </a>
                @endcan

                @can('gestionar_ordenes_trabajo')
                <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ (request()->routeIs('panel.operaciones.ordenes_trabajo.*') && !request()->routeIs('panel.operaciones.ordenes_trabajo.create') && !request()->routeIs('panel.operaciones.ordenes_trabajo.dashboard')) ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Órdenes de Trabajo">
                    <i class="fas fa-clipboard-list w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Órdenes de Trabajo</span>
                </a>
                <a href="{{ route('panel.colaboradores.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.colaboradores.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Tablero de Personal">
                    <i class="fas fa-users-cog w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Tablero de Personal</span>
                </a>
                @endcan

                @if(auth()->user()->hasRole('mecanico') || auth()->user()->hasRole('ayudante') || auth()->user()->hasRole('tecnico') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('recepcionista'))
                <a href="{{ route('panel.mis_tareas.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mis_tareas.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="{{ (auth()->user()->hasRole('admin') || auth()->user()->hasRole('recepcionista')) ? 'Tareas de Personal' : 'Mis Tareas' }}">
                    <i class="fas fa-tools w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">{{ (auth()->user()->hasRole('admin') || auth()->user()->hasRole('recepcionista')) ? 'Tareas de Personal' : 'Mis Tareas' }}</span>
                </a>
                @endif
                @endif

                {{-- Sección Inventario --}}
                @can('gestionar_inventario')
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider section-title whitespace-nowrap overflow-hidden">Logística</div>

                <a href="{{ route('panel.mantenimientos.categorias.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.categorias.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Categorías">
                    <i class="fas fa-tags w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Categorías</span>
                </a>

                <a href="{{ route('panel.mantenimientos.repuestos.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.repuestos.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Repuestos (SaaS)">
                    <i class="fas fa-boxes w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Repuestos (SaaS)</span>
                </a>

                @endcan

                {{-- Sección Mantenimientos (CRUDs) --}}
                @if(Gate::check('gestionar_marcas') || Gate::check('gestionar_versiones') || Gate::check('gestionar_sucursales'))
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider section-title whitespace-nowrap overflow-hidden">Mantenimientos</div>

                @can('gestionar_marcas')
                <a href="{{ route('panel.mantenimientos.marcas.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.marcas.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Marcas de Vehículos">
                    <i class="fas fa-tags w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Marcas de Vehículos</span>
                </a>
                @endcan

                @can('gestionar_versiones')
                <a href="{{ route('panel.mantenimientos.versiones.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.versiones.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Versiones de Modelos">
                    <i class="fas fa-code-branch w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Versiones de Modelos</span>
                </a>
                @endcan

                @can('gestionar_sucursales')
                <a href="{{ route('panel.mantenimientos.sucursales.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.sucursales.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Sucursales">
                    <i class="fas fa-store w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Sucursales</span>
                </a>
                @endcan

                <a href="{{ route('panel.mantenimientos.imagenes_landing.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.imagenes_landing.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Imágenes Landing">
                    <i class="fas fa-images w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Imágenes Landing</span>
                </a>
                @endif

                {{-- Sección Recursos Humanos --}}
                @if(Gate::check('ver_mi_qr') || Gate::check('ver_asistencias'))
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider section-title whitespace-nowrap overflow-hidden">Recursos Humanos</div>

                @can('ver_mi_qr')
                <a href="{{ route('panel.rrhh.asistencias.mi-qr') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.rrhh.asistencias.mi-qr') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Mi Credencial QR">
                    <i class="fas fa-qrcode w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Mi Credencial QR</span>
                </a>
                @endcan

                @can('ver_asistencias')
                <a href="{{ route('panel.rrhh.asistencias.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.rrhh.asistencias.index') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Control Asistencias">
                    <i class="fas fa-fingerprint w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Control Asistencias (Admin)</span>
                </a>
                @endcan
                @endif

                {{-- Sección Seguridad --}}
                @if(Gate::check('gestionar_roles') || Gate::check('gestionar_permisos') || Gate::check('gestionar_usuarios'))
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider section-title whitespace-nowrap overflow-hidden">Seguridad</div>

                @can('gestionar_roles')
                <a href="{{ route('panel.seguridad.roles.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.seguridad.roles.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Roles">
                    <i class="fas fa-user-shield w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Roles</span>
                </a>
                @endcan

                @can('gestionar_permisos')
                <a href="{{ route('panel.seguridad.permisos.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.seguridad.permisos.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Permisos">
                    <i class="fas fa-key w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Permisos</span>
                </a>
                @endcan

                @can('gestionar_usuarios')
                <a href="{{ route('panel.seguridad.usuarios.index') }}"
                    class="sidebar-menu-btn tooltip tooltip-right flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.seguridad.usuarios.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors" data-tip="Usuarios">
                    <i class="fas fa-users-cog w-5 text-center flex-shrink-0"></i>
                    <span class="menu-text whitespace-nowrap overflow-hidden text-ellipsis">Usuarios</span>
                </a>
                @endcan
                @endif
            </nav>
        </div>

        {{-- User Footer --}}
        <div class="border-t border-slate-700 p-4 bg-slate-900/50 flex flex-col items-center">
            <div class="flex items-center gap-3 w-full justify-center md:justify-start">
                <div class="avatar online placeholder flex-shrink-0">
                    <div class="bg-blue-600 text-white rounded-full w-10 h-10 shadow-md flex items-center justify-center">
                        <span class="font-bold text-sm">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0 user-info">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                    <p class="text-xs text-blue-400 truncate">Sesión Activa</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-3 w-full">
                @csrf
                <button
                    class="btn btn-sm btn-outline btn-error w-full gap-2 text-xs uppercase tracking-wide opacity-80 hover:opacity-100 sidebar-menu-btn tooltip tooltip-right" data-tip="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i> <span class="menu-text">Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">

        {{-- Topbar (Solo visible en desktop para acciones rápidas o breadcrumbs) --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
            <div class="flex items-center gap-4">
                {{-- Desktop Menu Toggle --}}
                <button id="desktop-menu-toggle" onclick="toggleDesktopMenu()"
                    class="hidden md:block text-gray-500 hover:text-blue-600 p-2 transition-transform duration-300">
                    <i class="fas fa-outdent text-xl" id="desktop-toggle-icon"></i>
                </button>

                {{-- Mobile Menu Trigger (Solo visible mobile) --}}
                <button id="mobile-menu-btn" onclick="toggleMobileMenu()"
                    class="md:hidden text-gray-500 hover:text-blue-600 p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                {{-- Breadcrumbs o Título --}}
                <div>
                    <h1 class="text-lg font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">
                        @yield('subtitle', 'Bienvenido al panel de gestión')</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Search --}}
                <div class="relative hidden sm:block">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="Buscar..."
                        class="input input-sm input-bordered w-full pl-9 bg-gray-50 focus:bg-white focus:border-blue-500 transition-colors rounded-full" />
                </div>

                {{-- Notifications --}}
                <div class="dropdown dropdown-end">
                    <div tabindex="0" role="button"
                        class="btn btn-ghost btn-circle btn-sm text-gray-500 hover:text-blue-600 hover:bg-blue-50 relative">
                        <i class="fas fa-bell text-lg"></i>
                        @if(Auth::user()->unreadNotifications->count() > 0)
                        <span
                            class="absolute top-1 right-1 h-3 w-3 bg-red-500 rounded-full border-2 border-white"></span>
                        @endif
                    </div>
                    <ul tabindex="0"
                        class="dropdown-content z-[20] menu p-2 shadow-xl bg-white border border-gray-100 rounded-box w-80 mt-2 text-sm">
                        <li
                            class="menu-title pt-3 pb-2 px-3 font-bold text-gray-800 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-lg">
                            <span>Notificaciones</span>
                            @if(Auth::user()->unreadNotifications->count() > 0)
                            <span class="badge badge-error badge-sm">{{ Auth::user()->unreadNotifications->count() }}
                                nuevas</span>
                            @endif
                        </li>
                        <li class="p-0">
                            <div class="max-h-64 overflow-y-auto p-0 w-full block">
                                <ul class="menu p-0 w-full">
                                    @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                                    <li class="border-b border-gray-50 last:border-0 rounded-none w-full">
                                        <a href="{{ $notification->data['url'] ?? '#' }}"
                                            class="py-3 px-4 flex flex-col items-start gap-1 whitespace-normal hover:bg-blue-50 transition-colors w-full rounded-none">
                                            <div class="flex items-start gap-3 w-full">
                                                <div class="mt-1 flex-shrink-0">
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                                                        <i class="fas fa-briefcase text-xs"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-gray-800 leading-tight text-xs">
                                                        {{ $notification->data['mensaje'] ?? 'Nueva notificación' }}
                                                    </p>
                                                    <p class="text-gray-400 text-[10px] mt-1"><i
                                                            class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    @empty
                                    <li
                                        class="py-6 px-4 text-center text-gray-400 flex flex-col items-center justify-center rounded-none bg-white hover:bg-white cursor-default">
                                        <i class="fas fa-bell-slash text-2xl mb-2 text-gray-200"></i>
                                        <span class="text-xs">Sin notificaciones nuevas</span>
                                    </li>
                                    @endforelse
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 md:p-8">
            @yield('content')
        </main>
    </div>

    @stack('modals')
    @stack('scripts')

    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobile-backdrop');

            // Toggle translate-x to slide in/out
            if (sidebar.classList.contains('-translate-x-full')) {
                // Open
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                // Close
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }

        function toggleDesktopMenu() {
            const sidebar = document.getElementById('sidebar');
            const bodyWrapper = document.body;
            const icon = document.getElementById('desktop-toggle-icon');

            sidebar.classList.toggle('sidebar-collapsed');
            bodyWrapper.classList.toggle('sidebar-collapsed-body');

            if (sidebar.classList.contains('sidebar-collapsed')) {
                icon.classList.remove('fa-outdent');
                icon.classList.add('fa-indent');
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                icon.classList.remove('fa-indent');
                icon.classList.add('fa-outdent');
                localStorage.setItem('sidebarCollapsed', 'false');
            }
        }

        // Initialize sidebar state on load
        document.addEventListener('DOMContentLoaded', () => {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                const sidebar = document.getElementById('sidebar');
                const bodyWrapper = document.body;
                const icon = document.getElementById('desktop-toggle-icon');

                sidebar.classList.add('sidebar-collapsed');
                bodyWrapper.classList.add('sidebar-collapsed-body');
                icon.classList.remove('fa-outdent');
                icon.classList.add('fa-indent');
            }
        });
    </script>
</body>

</html>