@extends('layouts.admin')

@section('content')
    <!-- Welcome Header is in the Layout now or we can keep a sub-header -->
    
    <!-- KPI Widgets -->
    <!-- KPI Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
        
        <!-- Widget: Vehículos -->
        <div class="relative overflow-hidden rounded-2xl bg-[#1a1a1a] border border-white/10 hover:border-[#00A3DA]/50 transition-all group shadow-lg flex flex-col justify-between h-48">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-car text-8xl text-white"></i>
            </div>
            <div class="p-6 relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-lg bg-[#00A3DA]/10 text-[#00A3DA]">
                        <i class="fas fa-car text-xl"></i>
                    </div>
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em]">Vehículos</h3>
                </div>
                <div>
                    <div class="flex items-end gap-3">
                        <span class="text-5xl font-bold text-white tracking-tighter">128</span>
                        <span class="text-xs text-green-400 mb-2 font-bold bg-green-500/10 px-2 py-0.5 rounded-full flex items-center">
                            <i class="fas fa-arrow-up text-[0.6rem] mr-1"></i> 2.5%
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">En taller actualmente</p>
                </div>
            </div>
            <div class="h-1 w-full bg-[#121212]">
                 <div class="h-full bg-[#00A3DA]" style="width: 70%"></div>
            </div>
        </div>

        <!-- Widget: Ordenes -->
        <div class="relative overflow-hidden rounded-2xl bg-[#1a1a1a] border border-white/10 hover:border-[#003399]/50 transition-all group shadow-lg flex flex-col justify-between h-48">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-clipboard-list text-8xl text-white"></i>
            </div>
             <div class="p-6 relative z-10 flex flex-col h-full justify-between">
                 <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-lg bg-[#003399]/10 text-[#003399]">
                        <i class="fas fa-tasks text-xl"></i>
                    </div>
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em]">Ordenes</h3>
                </div>
                <div>
                    <div class="flex items-end gap-3">
                         <span class="text-5xl font-bold text-white tracking-tighter">45</span>
                         <span class="text-xs text-gray-500 mb-2 font-bold bg-white/5 px-2 py-0.5 rounded-full">Activas</span>
                    </div>
                     <p class="text-xs text-gray-500 mt-1">Requieren atención</p>
                </div>
            </div>
            <div class="h-1 w-full bg-[#121212]">
                 <div class="h-full bg-[#003399]" style="width: 45%"></div>
            </div>
        </div>

        <!-- Widget: Citas -->
        <div class="relative overflow-hidden rounded-2xl bg-[#1a1a1a] border border-white/10 hover:border-[#DF0012]/50 transition-all group shadow-lg flex flex-col justify-between h-48">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-calendar-check text-8xl text-white"></i>
            </div>
            <div class="p-6 relative z-10 flex flex-col h-full justify-between">
                <div class="flex items-center gap-3">
                     <div class="p-2.5 rounded-lg bg-[#DF0012]/10 text-[#DF0012]">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em]">Citas</h3>
                </div>
                <div>
                     <div class="flex items-end gap-3">
                        <span class="text-5xl font-bold text-white tracking-tighter">12</span>
                         <span class="text-xs text-white mb-2 font-bold bg-[#DF0012] px-2 py-0.5 rounded-full shadow-lg shadow-red-900/50">HOY</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Programadas para hoy</p>
                </div>
            </div>
            <div class="h-1 w-full bg-[#121212]">
                 <div class="h-full bg-[#DF0012]" style="width: 85%"></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Grid (Aligned: 6 cols matches 3 cols of top) -->
    <div>
        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-[0.1em] mb-6 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-[#00A3DA]"></span>
            Acciones Rápidas
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            
            <a href="#" class="flex flex-col items-center justify-center p-6 bg-[#1a1a1a] rounded-2xl border border-white/5 hover:border-[#003399] hover:bg-[#202020] transition-all duration-300 group shadow-md hover:shadow-xl hover:shadow-blue-900/10">
                <div class="h-14 w-14 rounded-full bg-[#121212] border border-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-plus text-[#00A3DA] text-xl"></i>
                </div>
                <span class="text-[0.7rem] font-bold text-gray-400 group-hover:text-white uppercase tracking-wider text-center">Ingreso</span>
            </a>

            <a href="#" class="flex flex-col items-center justify-center p-6 bg-[#1a1a1a] rounded-2xl border border-white/5 hover:border-[#DF0012] hover:bg-[#202020] transition-all duration-300 group shadow-md hover:shadow-xl hover:shadow-red-900/10 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-12 h-12 bg-gradient-to-bl from-[#DF0012]/20 to-transparent rounded-bl-full -mr-4 -mt-4"></div>
                <div class="h-14 w-14 rounded-full bg-[#121212] border border-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-tools text-[#DF0012] text-xl"></i>
                </div>
                <span class="text-[0.7rem] font-bold text-gray-400 group-hover:text-white uppercase tracking-wider text-center">Nueva Orden</span>
            </a>

            <a href="#" class="flex flex-col items-center justify-center p-6 bg-[#1a1a1a] rounded-2xl border border-white/5 hover:border-[#00A3DA] hover:bg-[#202020] transition-all duration-300 group shadow-md hover:shadow-xl hover:shadow-blue-900/10">
                <div class="h-14 w-14 rounded-full bg-[#121212] border border-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-search text-gray-400 group-hover:text-white text-xl"></i>
                </div>
                <span class="text-[0.7rem] font-bold text-gray-400 group-hover:text-white uppercase tracking-wider text-center">Consultar</span>
            </a>

            <a href="#" class="flex flex-col items-center justify-center p-6 bg-[#1a1a1a] rounded-2xl border border-white/5 hover:border-gray-500 hover:bg-[#202020] transition-all duration-300 group shadow-md hover:shadow-xl">
                <div class="h-14 w-14 rounded-full bg-[#121212] border border-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-user-plus text-gray-400 group-hover:text-white text-xl"></i>
                </div>
                <span class="text-[0.7rem] font-bold text-gray-400 group-hover:text-white uppercase tracking-wider text-center">Cliente</span>
            </a>

             <a href="#" class="flex flex-col items-center justify-center p-6 bg-[#1a1a1a] rounded-2xl border border-white/5 hover:border-purple-500 hover:bg-[#202020] transition-all duration-300 group shadow-md hover:shadow-xl hover:shadow-purple-900/10">
                <div class="h-14 w-14 rounded-full bg-[#121212] border border-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-calendar-alt text-gray-400 group-hover:text-white text-xl"></i>
                </div>
                <span class="text-[0.7rem] font-bold text-gray-400 group-hover:text-white uppercase tracking-wider text-center">Citas</span>
            </a>

             <a href="#" class="flex flex-col items-center justify-center p-6 bg-[#1a1a1a] rounded-2xl border border-white/5 hover:border-green-500 hover:bg-[#202020] transition-all duration-300 group shadow-md hover:shadow-xl hover:shadow-green-900/10">
                <div class="h-14 w-14 rounded-full bg-[#121212] border border-white/5 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                    <i class="fas fa-boxes text-gray-400 group-hover:text-white text-xl"></i>
                </div>
                <span class="text-[0.7rem] font-bold text-gray-400 group-hover:text-white uppercase tracking-wider text-center">Inventario</span>
            </a>

        </div>
    </div>
@endsection
