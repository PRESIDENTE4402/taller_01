<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BMW Service') }} - Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-[#050505] text-gray-300 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex bg-[#050505] relative overflow-hidden">
        
        <!-- Background Ambient Effect -->
        <div class="fixed inset-0 pointer-events-none z-0">
             <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-[#003399]/20 rounded-full blur-[120px] opacity-20 animate-pulse"></div>
             <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-[#DF0012]/10 rounded-full blur-[120px] opacity-20"></div>
        </div>

        <!-- Sidebar (Desktop & Mobile) -->
        <!-- Fixed position on mobile (z-60), Relative on desktop. Transform for transition. -->
        <!-- Sidebar (Desktop & Mobile) -->
        <!-- Fixed position on mobile (z-60), Relative on desktop. Transform for transition. -->
        <aside class="fixed inset-y-0 left-0 z-[60] w-72 bg-[#0a0a0a] border-r border-white/5 transition-transform duration-300 ease-out transform lg:relative lg:translate-x-0 flex flex-col shadow-2xl"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            @keydown.escape.window="sidebarOpen = false">

            <!-- Mobile Close Button (Top Right of Sidebar) -->
            <div class="absolute top-4 right-4 lg:hidden">
                <button @click="sidebarOpen = false" class="text-gray-400 hover:text-white transition-colors p-3 rounded-full hover:bg-white/10 active:scale-95">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Logo Area -->
            <div class="flex items-center justify-center h-24 border-b border-white/5 bg-[#0a0a0a]">
                <div class="flex items-center gap-3">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/44/BMW.svg/2048px-BMW.svg.png"
                        alt="BMW Logo" class="h-10 w-auto drop-shadow-2xl">
                    <div class="flex flex-col">
                        <span class="font-bold text-xl tracking-[0.2em] text-white leading-none">BMW</span>
                        <span class="text-[0.6rem] tracking-[0.3em] text-gray-500 uppercase mt-1">Service Center</span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto custom-scrollbar">
                
                <p class="px-3 text-[0.65rem] font-bold text-gray-600 uppercase tracking-[0.2em] mb-4">Principal</p>
                
                <a href="{{ route('dashboard') }}"
                    class="flex items-center px-3 py-3 rounded-lg transition-all duration-200 group border border-transparent {{ request()->routeIs('dashboard') ? 'bg-white/5 text-white border-white/5 shadow-inner' : 'text-gray-400 hover:bg-white/5 hover:text-gray-200 hover:border-white/5' }}">
                    <div class="w-8 flex justify-center">
                         <i class="fas fa-home text-sm {{ request()->routeIs('dashboard') ? 'text-[#00A3DA]' : 'text-gray-500 group-hover:text-[#00A3DA] transition-colors' }}"></i>
                    </div>
                    <span class="text-sm font-medium tracking-wide">Panel de Control</span>
                </a>

                <p class="px-3 text-[0.65rem] font-bold text-gray-600 uppercase tracking-[0.2em] mb-4 mt-8">Operativa</p>
                
                <a href="#" class="flex items-center px-3 py-3 rounded-lg text-gray-400 hover:bg-white/5 hover:text-gray-200 border border-transparent hover:border-white/5 transition-all duration-200 group">
                    <div class="w-8 flex justify-center">
                        <i class="fas fa-car text-sm text-gray-500 group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="text-sm font-medium">Vehículos</span>
                </a>

                <a href="#" class="flex items-center px-3 py-3 rounded-lg text-gray-400 hover:bg-white/5 hover:text-gray-200 border border-transparent hover:border-white/5 transition-all duration-200 group">
                     <div class="w-8 flex justify-center">
                        <i class="fas fa-clipboard-list text-sm text-gray-500 group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="text-sm font-medium">Ordenes de Trabajo</span>
                </a>
               
                <a href="#" class="flex items-center px-3 py-3 rounded-lg text-gray-400 hover:bg-white/5 hover:text-gray-200 border border-transparent hover:border-white/5 transition-all duration-200 group">
                     <div class="w-8 flex justify-center">
                        <i class="fas fa-users text-sm text-gray-500 group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="text-sm font-medium">Clientes</span>
                </a>

                 <p class="px-3 text-[0.65rem] font-bold text-gray-600 uppercase tracking-[0.2em] mb-4 mt-8">Administración</p>

                <a href="#" class="flex items-center px-3 py-3 rounded-lg text-gray-400 hover:bg-white/5 hover:text-gray-200 border border-transparent hover:border-white/5 transition-all duration-200 group">
                    <div class="w-8 flex justify-center">
                        <i class="fas fa-cog text-sm text-gray-500 group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="text-sm font-medium">Configuración</span>
                </a>
            </nav>
            
            <!-- User Profile (Bottom) -->
             <div class="p-4 border-t border-white/5 bg-[#0a0a0a]">
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/5 transition-colors cursor-pointer group">
                    <div class="relative">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-b from-[#1a1a1a] to-[#0a0a0a] flex items-center justify-center text-white border border-white/10 group-hover:border-[#00A3DA]/50 transition-colors">
                            <span class="text-sm font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-[#0a0a0a]"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[0.65rem] text-gray-500 truncate uppercase tracking-wider group-hover:text-[#00A3DA] transition-colors">Administrador</p>
                    </div>
                     <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-white transition-colors">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Mobile Overlay -->
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm lg:hidden" 
             x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             @click="sidebarOpen = false"></div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden relative z-10 w-full bg-[#0a0a0a]">
            
            <!-- Top Header (Sticky & Functional) -->
            <header class="h-24 flex items-center justify-between px-6 lg:px-10 bg-[#0a0a0a]/80 backdrop-blur-md border-b border-white/5 sticky top-0 z-40">
                
                <!-- Left: Title & Context -->
                <div class="flex items-center gap-4">
                     <!-- Hamburger Button (Visible on Mobile) -->
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 hover:text-white focus:outline-none lg:hidden transition-all duration-200 p-3 hover:bg-white/10 rounded-lg">
                        <i class="fas fa-bars text-3xl"></i>
                    </button>

                    <div class="flex flex-col">
                        <div class="flex items-center text-[0.65rem] text-gray-500 space-x-2 tracking-wider uppercase mb-0.5">
                            <span>BMW Service</span>
                            <i class="fas fa-chevron-right text-[0.5rem] text-gray-600"></i>
                            <span class="text-[#00A3DA] font-semibold">Dashboard</span>
                        </div>
                        <h2 class="text-2xl font-semibold text-white tracking-tight">Panel Principal</h2>
                    </div>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-6 lg:gap-10">
                    
                    <!-- Search Bar (Larger & More Visible) -->
                    <div class="hidden md:block relative group">
                        <!-- Glow -->
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-[#00A3DA]/40 to-[#003399]/40 rounded-xl opacity-0 group-hover:opacity-100 transition duration-500 blur"></div>
                        
                        <!-- Input -->
                        <div class="relative flex items-center bg-[#252525] rounded-xl px-5 py-3.5 border border-white/20 w-80 lg:w-[32rem] transition-all focus-within:border-[#00A3DA] focus-within:bg-[#2a2a2a] focus-within:shadow-glow shadow-inner">
                             <i class="fas fa-search text-gray-400 text-lg mr-4"></i>
                            <input class="bg-transparent border-none text-base text-white placeholder-gray-400 focus:ring-0 w-full p-0 font-medium leading-relaxed" 
                                   type="text" placeholder="Buscar orden, chasis o cliente...">
                            <div class="hidden lg:flex items-center gap-1.5 ml-2">
                                <span class="text-[0.65rem] font-bold text-gray-400 border border-gray-600 rounded-md px-2 py-1 bg-[#1a1a1a]">CMD</span>
                                <span class="text-[0.65rem] font-bold text-gray-400 border border-gray-600 rounded-md px-2 py-1 bg-[#1a1a1a]">K</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notification -->
                    <button class="relative p-3 rounded-xl text-gray-400 hover:text-white hover:bg-white/10 transition-all border border-transparent hover:border-white/10 group">
                        <i class="far fa-bell text-2xl group-hover:scale-110 transition-transform"></i>
                        <span class="absolute top-2.5 right-3 h-3 w-3 rounded-full bg-[#DF0012] border-2 border-[#0a0a0a] shadow-sm shadow-red-900/50"></span>
                    </button>
                    
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#050505]">
                <div class="container mx-auto px-6 lg:px-10 py-8">
                     @yield('content')
                </div>
            </main>

        </div>

    </div>

</body>

</html>
