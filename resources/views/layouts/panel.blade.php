<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; } /* Fondo gris suave */
        
        /* Sidebar personalizado oscuro profesional */
        .sidebar-container {
            background-color: #1e293b; /* Slate 800 */
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
            background-color: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
    </style>
</head>
<body class="h-screen overflow-hidden flex text-gray-800 bg-gray-50">

    {{-- SIDEBAR / MENÚ LATERAL OSCURO --}}
    <aside class="w-64 sidebar-container shadow-xl flex-shrink-0 flex flex-col transition-all duration-300 hidden md:flex z-20">
        {{-- Logo Area --}}
        <div class="h-16 flex items-center px-6 border-b border-slate-700 bg-slate-900">
            <div class="flex items-center gap-3 font-bold text-xl tracking-tight text-white">
                <div class="w-8 h-8 rounded bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-lg">
                    <i class="fas fa-wrench text-xs text-white"></i>
                </div>
                <span>Taller<span class="text-blue-400">Pro</span></span>
            </div>
        </div>

        {{-- Scrollable Menu --}}
        <div class="flex-1 overflow-y-auto py-4 sidebar-scroll">
            <nav class="space-y-1 px-3">
                
                {{-- Sección Principal --}}
                <div class="px-3 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Principal</div>
                
                <a href="{{ route('panel.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.dashboard') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors">
                    <i class="fas fa-chart-pie w-5 text-center"></i>
                    Dashboard
                </a>

                {{-- Sección Operaciones --}}
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Operaciones</div>
                
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md sidebar-item text-slate-400 transition-colors">
                    <i class="fas fa-calendar-check w-5 text-center"></i>
                    Gestionar Citas
                </a>
                
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md sidebar-item text-slate-400 transition-colors">
                    <i class="fas fa-car w-5 text-center"></i>
                    Recepción Vehículos
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md sidebar-item text-slate-400 transition-colors">
                    <i class="fas fa-clipboard-list w-5 text-center"></i>
                    Órdenes de Trabajo
                </a>

                {{-- Sección Inventario --}}
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Logística</div>
                
                <a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md sidebar-item text-slate-400 transition-colors">
                    <i class="fas fa-boxes w-5 text-center"></i>
                    Inventario Repuestos
                </a>

                {{-- Sección Mantenimientos (CRUDs) --}}
                <div class="px-3 mt-6 mb-2 text-xs font-bold text-slate-500 uppercase tracking-wider">Mantenimientos</div>
                
                <a href="{{ route('panel.mantenimientos.marcas.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.marcas.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors">
                    <i class="fas fa-tags w-5 text-center"></i>
                    Marcas de Vehículos
                </a>
                
                <a href="{{ route('panel.mantenimientos.versiones.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('panel.mantenimientos.versiones.*') ? 'sidebar-active' : 'sidebar-item text-slate-400' }} transition-colors">
                    <i class="fas fa-code-branch w-5 text-center"></i>
                    Versiones de Modelos
                </a>
            </nav>
        </div>

        {{-- User Footer --}}
        <div class="border-t border-slate-700 p-4 bg-slate-900/50">
            <div class="flex items-center gap-3">
                <div class="avatar online placeholder">
                    <div class="bg-blue-600 text-white rounded-full w-10 shadow-md flex items-center justify-center">
                        <span class="font-bold text-sm">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                    <p class="text-xs text-blue-400 truncate">Sesión Activa</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button class="btn btn-sm btn-outline btn-error w-full gap-2 text-xs uppercase tracking-wide opacity-80 hover:opacity-100">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        {{-- Topbar (Solo visible en desktop para acciones rápidas o breadcrumbs) --}}
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-10">
            <div class="flex items-center gap-4">
                {{-- Mobile Menu Trigger (Solo visible mobile) --}}
                <button class="md:hidden text-gray-500 hover:text-blue-600 p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                {{-- Breadcrumbs o Título --}}
                <div>
                    <h1 class="text-lg font-bold text-gray-800">@yield('title', 'Dashboard')</h1>
                    <p class="text-xs text-gray-500 hidden sm:block">@yield('subtitle', 'Bienvenido al panel de gestión')</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                {{-- Search --}}
                <div class="relative hidden sm:block">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="Buscar..." class="input input-sm input-bordered w-full pl-9 bg-gray-50 focus:bg-white focus:border-blue-500 transition-colors rounded-full" />
                </div>

                {{-- Notifications --}}
                <button class="btn btn-ghost btn-circle btn-sm text-gray-500 hover:text-blue-600 hover:bg-blue-50 relative">
                    <i class="fas fa-bell text-lg"></i>
                    <span class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
                </button>
            </div>
        </header>

        {{-- Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 md:p-8">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
