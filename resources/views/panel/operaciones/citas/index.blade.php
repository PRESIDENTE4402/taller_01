@extends('layouts.panel')

@section('title', 'Gestionar Citas')
@section('subtitle', 'Visualiza y administra la agenda del taller')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-full">

        <!-- Sidebar de Filtros y Resumen -->
        <div class="lg:col-span-1 flex flex-col gap-3">

            <!-- Filtros Rapidos -->
            <div class="bg-white rounded-xl shadow-sm p-3">
                <h4 class="font-bold text-gray-700 mb-2 flex justify-between items-center text-xs uppercase">
                    <span>Rango de Fechas</span>
                    <button onclick="clearDateFilters()" class="text-xs text-red-400 hover:text-red-600 font-medium"
                        title="Limpiar"><i class="fas fa-times"></i> Limpiar</button>
                </h4>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="text-[9px] uppercase font-black text-gray-400 mb-0.5 block">Desde</label>
                        <input type="date" id="dateStart"
                            class="w-full text-[11px] border border-gray-200 rounded-lg py-1 px-2 text-gray-600 font-medium focus:ring-1 focus:ring-blue-500 outline-none"
                            onchange="loadCitas()">
                    </div>
                    <div>
                        <label class="text-[9px] uppercase font-black text-gray-400 mb-0.5 block">Hasta</label>
                        <input type="date" id="dateEnd"
                            class="w-full text-[11px] border border-gray-200 rounded-lg py-1 px-2 text-gray-600 font-medium focus:ring-1 focus:ring-blue-500 outline-none"
                            onchange="loadCitas()">
                    </div>
                </div>



                <h4 class="font-black text-gray-400 mb-1 border-t border-gray-100 pt-2 text-[9px] uppercase">Sucursal</h4>
                <select id="filterSucursal"
                    class="w-full text-[11px] border border-gray-200 rounded-lg py-1.5 px-2 text-gray-600 font-bold focus:ring-1 focus:ring-blue-500 outline-none mb-2 bg-gray-50/50"
                    onchange="loadCitas()">
                    <option value="all">Todas</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>

                <h4 class="font-black text-gray-400 mb-2 border-t border-gray-100 pt-2 text-[9px] uppercase">Estado</h4>
                <div class="space-y-1">
                    <button onclick="filterCitas('pendiente')"
                        class="w-full text-left px-2 py-1.5 rounded-lg hover:bg-gray-50 text-[11px] font-bold text-gray-600 flex justify-between items-center group transition-colors filter-btn"
                        data-status="pendiente">
                        <span><i class="fas fa-circle text-[8px] text-yellow-400 mr-2"></i>Web (Pend.)</span>
                        <span class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-[9px] font-black group-hover:bg-yellow-100 group-hover:text-yellow-700" id="badge-pendiente">-</span>
                    </button>
                    <button onclick="filterCitas('confirmada')"
                        class="w-full text-left px-2 py-1.5 rounded-lg hover:bg-gray-50 text-[11px] font-bold text-gray-600 flex justify-between items-center group transition-colors filter-btn"
                        data-status="confirmada">
                        <span><i class="fas fa-circle text-[8px] text-blue-500 mr-2"></i>Confirmadas</span>
                        <span class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-[9px] font-black group-hover:bg-blue-100 group-hover:text-blue-700" id="badge-confirmada">-</span>
                    </button>
                    <button onclick="filterCitas('concretada')"
                        class="w-full text-left px-2 py-1.5 rounded-lg hover:bg-gray-50 text-[11px] font-bold text-gray-600 flex justify-between items-center group transition-colors filter-btn"
                        data-status="concretada">
                        <span><i class="fas fa-circle text-[8px] text-green-500 mr-2"></i>En Taller</span>
                        <span class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-[9px] font-black group-hover:bg-green-100 group-hover:text-green-700" id="badge-concretada">-</span>
                    </button>
                    <button onclick="filterCitas('all')"
                        class="w-full text-center py-1.5 rounded-lg bg-blue-50 text-blue-700 text-[10px] font-black ring-1 ring-blue-100 mt-2 filter-btn active uppercase tracking-wider"
                        data-status="all">
                        Ver Todas
                    </button>
                </div>
            </div>

            <!-- Widget Capacidad -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-100">
                <h4 class="font-black text-gray-400 text-[9px] uppercase mb-2 flex justify-between items-center">
                    <span>Ocupación (<span id="capacityDateLabel">--</span>)</span>
                    <i class="fas fa-chart-pie text-blue-300"></i>
                </h4>
                <div id="capacityContainer" class="space-y-2">
                    <!-- Injected via JS -->
                    <p class="text-[10px] text-gray-400 text-center py-1">Cargando...</p>
                </div>
            </div>



            <!-- Mini Calendario -->
            <div class="bg-white rounded-xl shadow-sm p-3 border border-gray-100">
                <h4 class="font-bold text-gray-700 mb-2 text-xs flex justify-between items-center">
                    <span id="miniCalendarTitle">Marzo 2026</span>
                    <div class="flex gap-2">
                        <button onclick="prevMonth()" class="text-gray-400 hover:text-blue-600 px-1"><i class="fas fa-chevron-left text-[10px]"></i></button>
                        <button onclick="nextMonth()" class="text-gray-400 hover:text-blue-600 px-1"><i class="fas fa-chevron-right text-[10px]"></i></button>
                    </div>
                </h4>
                <div class="grid grid-cols-7 gap-0.5 text-center text-[9px] font-black text-gray-300 mb-1">
                    <div>DO</div><div>LU</div><div>MA</div><div>MI</div><div>JU</div><div>VI</div><div>SA</div>
                </div>
                <div class="grid grid-cols-7 gap-0.5 text-center" id="miniCalendarGrid">
                    <!-- Days injected via JS -->
                </div>
            </div>
        </div>

        <!-- Lista de Citas (Agenda) -->
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[calc(100vh-140px)]">
            <!-- Header Tabla -->
            <div
                class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center bg-gray-50/50 rounded-t-xl gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        <span id="agendaTitle">Agenda General</span>
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Quick Date Filters (Moved here) -->
                    <div class="flex bg-white rounded-lg p-1 border border-gray-200 shadow-sm">
                        <button onclick="setDateFilter('today')"
                            class="px-4 py-1.5 rounded-md text-xs font-bold text-gray-600 hover:bg-gray-50 transition-colors" id="btnDateToday">
                            Hoy
                        </button>
                        <button onclick="setDateFilter('tomorrow')"
                            class="px-4 py-1.5 rounded-md text-xs font-bold text-blue-600 hover:bg-blue-50 transition-colors flex items-center gap-1" id="btnDateTomorrow">
                            <i class="fas fa-bell text-[10px]"></i> Mañana
                        </button>
                    </div>

                    <!-- Resumen Hoy -->
                    <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Hoy</p>
                            <p class="text-xs text-gray-500 font-medium">Pendientes de recibir</p>
                        </div>
                        <div class="h-8 w-px bg-gray-200"></div>
                        <span class="text-3xl font-black text-blue-600 leading-none" id="countToday">-</span>
                    </div>
                </div>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-2 space-y-3 custom-scrollbar bg-gray-50/30" id="citasContainer">
                <!-- Loading State -->
                <div class="text-center py-20 text-gray-400 animate-pulse">
                    <i class="fas fa-circle-notch fa-spin text-3xl mb-3"></i>
                    <p>Cargando agenda...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalle Cita -->
    <div id="citaModal"
        class="fixed inset-0 bg-black/60 z-50 hidden backdrop-blur-sm transition-opacity opacity-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform scale-95 transition-transform duration-300 overflow-hidden relative"
            id="citaModalContent">

            <!-- Header Decorativo -->
            <div class="h-24 bg-gradient-to-br from-gray-900 to-gray-800 relative">
                <button onclick="closeCitaModal()"
                    class="absolute top-4 right-4 text-white/70 hover:text-white transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="px-6 pb-6 -mt-12 relative z-10">
                <!-- Tarjeta Principal -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-5 text-center mb-6">
                    <div class="flex justify-center -mt-8 mb-3">
                        <span id="modalStatusBadge" class="shadow-md">PENDIENTE</span>
                    </div>
                    <h3 class="text-xl font-black text-gray-800 leading-tight mb-1" id="modalClienteName">Cliente</h3>
                    <p class="text-sm font-medium text-gray-500 bg-gray-50 inline-block px-3 py-1 rounded-full border border-gray-100"
                        id="modalVehiculoInfo">Vehículo</p>
                    
                    <!-- Alerta Vehículo Manual -->
                    <div id="manualVehicleWarning" class="hidden max-w-[280px] mx-auto"></div>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                            <i class="far fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-bold uppercase">Fecha y Hora</p>
                            <p class="font-semibold text-gray-800" id="modalFecha">--</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div
                            class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0 mt-1">
                            <i class="fas fa-wrench"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-bold uppercase">Motivo / Requerimiento</p>
                            <p class="text-sm text-gray-700 leading-relaxed" id="modalMotivo">--</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div
                            class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                            <i class="fas fa-envelope"></i> <!-- Changed icon to represent contact general or keep phone -->
                            <!-- Actually keep phone icon or use address-card -->
                            <i class="fas fa-address-card absolute opacity-0"></i> <!-- Hack -->
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="flex-1 min-w-0"> <!-- Added flex layout -->
                            <p class="text-xs text-gray-500 font-bold uppercase">Contacto</p>
                            <a href="#" id="modalPhoneLink"
                                class="font-semibold text-blue-600 hover:underline block leading-tight truncate">--</a>
                            <a href="#" id="modalEmailLink"
                                class="text-xs text-gray-400 block mt-0.5 hover:text-blue-500 truncate"
                                title="Enviar Correo">--</a>
                        </div>
                    </div>

                    <!-- Communication Actions -->
                    <div class="grid grid-cols-2 gap-3">
                        <a href="#" id="btnWhatsApp" target="_blank"
                            class="flex items-center gap-3 p-3 rounded-xl bg-green-50 text-green-600 hover:bg-green-100 hover:text-green-700 transition-colors border border-green-100 group">
                            <i class="fab fa-whatsapp text-2xl group-hover:scale-110 transition-transform"></i>
                            <span class="text-[10px] font-black uppercase">WhatsApp</span>
                        </a>
                        <button type="button" id="btnReminder"
                            class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 transition-colors border border-blue-100 group w-full text-left">
                            <i class="fas fa-bell text-2xl group-hover:scale-110 transition-transform"></i>
                            <span class="text-[10px] font-black uppercase leading-tight">Recordatorio<br>WhatsApp</span>
                        </button>
                        <button type="button" id="btnEmailNotify"
                            class="flex items-center gap-3 p-3 rounded-xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 hover:text-indigo-700 transition-colors border border-indigo-100 group w-full text-left">
                            <i class="fas fa-envelope text-2xl group-hover:scale-110 transition-transform"></i>
                            <span class="text-[10px] font-black uppercase">Enviar<br>Correo</span>
                        </button>
                        <a href="#" id="btnCall"
                            class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-colors border border-gray-100 group">
                            <i class="fas fa-phone-alt text-2xl group-hover:scale-110 transition-transform"></i>
                            <span class="text-[10px] font-black uppercase">Llamar</span>
                        </a>
                    </div>
                </div>

                <div class="flex flex-col gap-3" id="modalActions">
                    <!-- Actions injected via JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear Cita Manual -->
    <div id="crearCitaModal"
        class="fixed inset-0 bg-black/60 z-50 hidden backdrop-blur-sm transition-opacity opacity-0 flex items-end sm:items-center justify-center p-0 sm:p-4">
        <form id="crearCitaForm"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-2xl transform translate-y-full sm:translate-y-0 scale-95 transition-all duration-300 h-[90vh] sm:h-auto sm:max-h-[90vh] flex flex-col">

            <!-- Header -->
            <div
                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-2xl flex-shrink-0">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-600"></i> Nueva Cita
                </h3>
                <button type="button" onclick="closeManualCitaModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Body Scrollable -->
            <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">

                <!-- Toggle Mode -->
                <div class="flex bg-gray-100 p-1 rounded-xl mb-6">
                    <button type="button" onclick="setCreationMode('buscar')" id="btnModeBuscar"
                        class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-700 bg-white shadow-sm transition-all">
                        Buscar Cliente
                    </button>
                    <button type="button" onclick="setCreationMode('nuevo')" id="btnModeNuevo"
                        class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-gray-700 transition-all">
                        Nuevo Cliente
                    </button>
                </div>

                <input type="hidden" name="modo_creacion" id="modoCreacion" value="buscar">

                <!-- SECCIÓN 1: BUSCAR CLIENTE -->
                <div id="sectionBuscarCliente" class="mb-6 relative">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Buscar Cliente</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" id="searchClientInput"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 pl-10 pr-4 focus:ring-2 focus:ring-blue-500 outline-none transition-all"
                            placeholder="Nombre, teléfono o email..." oninput="debounceSearchClient()">
                        <!-- Dropdown Resultados -->
                        <div id="clientSearchResults"
                            class="absolute top-full left-0 w-full bg-white border border-gray-100 shadow-xl rounded-xl mt-1 z-30 hidden max-h-60 overflow-y-auto">
                        </div>
                    </div>

                    <!-- Cliente Seleccionado -->
                    <div id="selectedClientCard"
                        class="mt-3 hidden bg-blue-50 border border-blue-100 rounded-xl p-3 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-200 text-blue-600 flex items-center justify-center font-bold">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800" id="selectedClientName">Nombre Cliente</p>
                                <p class="text-xs text-blue-600" id="selectedClientPhone">Teléfono</p>
                            </div>
                        </div>
                        <button type="button" onclick="clearSelectedClient()" class="text-red-400 hover:text-red-600">
                            <i class="fas fa-times-circle"></i>
                        </button>
                        <input type="hidden" name="cliente_id" id="cliente_id">
                    </div>
                </div>

                <!-- SECCIÓN 2: NUEVO CLIENTE (Inicialmente Oculto) -->
                <div id="sectionNuevoCliente"
                    class="hidden space-y-4 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <h4 class="text-sm font-bold text-blue-600 mb-2 border-b border-gray-200 pb-2">Datos Personales</h4>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                        <input type="text" name="nombre_nuevo"
                            class="input-new-client w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                            <input type="text" name="telefono_nuevo"
                                class="input-new-client w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email (Opcional)</label>
                            <input type="email" name="email_nuevo"
                                class="input-new-client w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Vehículo -->
                    <div class="col-span-1 md:col-span-2 space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-bold text-gray-500 uppercase">Vehículo</label>
                            <button type="button" id="btnToggleNewVehicle" onclick="toggleNewVehicleMode()"
                                class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors hidden">
                                <i class="fas fa-plus"></i> Nuevo Vehículo
                            </button>
                        </div>

                        <!-- Vehículo SELECT (Modo Buscar) -->
                        <div id="vehiculoSelectContainer">
                            <select name="vehiculo_id" id="vehiculoSelect"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 outline-none appearance-none disabled:opacity-50"
                                disabled>
                                <option value="">Primero selecciona un cliente...</option>
                            </select>
                        </div>

                        <!-- Vehículo INPUTS (Modo Nuevo) -->
                        <div id="vehiculoNewContainer" class="hidden p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <h4 class="text-sm font-bold text-blue-600 mb-2 border-b border-gray-200 pb-2">Datos del
                                Vehículo</h4>
                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Placa</label>
                                    <input type="text" name="placa_nuevo" placeholder="Ej: P-123ABC"
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none uppercase">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Año</label>
                                    <input type="number" name="anio_nuevo" placeholder="Ej: 2020"
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Marca -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Marca</label>
                                    <select id="marcaNuevoSelect" name="marca_nuevo_select"
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none"
                                        onchange="checkMarcaManual(this)">
                                        <option value="">Cargando...</option>
                                    </select>
                                    <input type="text" id="marcaNuevoInput" name="marca_nuevo"
                                        placeholder="Escriba la marca..."
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none mt-2 hidden uppercase">
                                </div>

                                <!-- Modelo -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Modelo</label>
                                    <select id="modeloNuevoSelect" name="modelo_nuevo_select"
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none"
                                        disabled onchange="checkModeloManual(this)">
                                        <option value="">Seleccione Marca...</option>
                                    </select>
                                    <input type="text" id="modeloNuevoInput" name="modelo_nuevo"
                                        placeholder="Escriba el modelo..."
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none mt-2 hidden uppercase">
                                </div>

                                <!-- Version -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Versión</label>
                                    <select id="versionNuevoSelect" name="version_nuevo_select"
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none"
                                        disabled onchange="checkVersionManual(this)">
                                        <option value="">Seleccione Modelo...</option>
                                    </select>
                                    <input type="text" id="versionNuevoInput" name="version_nuevo"
                                        placeholder="Escriba la versión..."
                                        class="input-new-vehicle w-full bg-white border border-gray-200 rounded-lg py-2 px-3 focus:ring-2 focus:ring-blue-500 outline-none mt-2 hidden uppercase">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sucursal -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Sucursal</label>
                        <select name="sucursal_id"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                            @foreach($sucursales as $sucursal)
                                <option value="{{ $sucursal->id }}">{{ $sucursal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fecha y Hora -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Fecha</label>
                        <input type="date" name="fecha" id="inputFecha"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Hora</label>
                        <input type="time" name="hora" id="inputHora"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 outline-none"
                            required>
                    </div>
                </div>

                <!-- Motivo -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Motivo de la Cita</label>
                    <textarea name="motivo" rows="3"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="Ej: Mantenimiento 10,000 km, Revisión frenos..." required></textarea>
                </div>

            </div>

            <!-- Footer -->
            <div class="p-6 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end gap-3">
                <button type="button" onclick="closeManualCitaModal()"
                    class="px-5 py-2.5 rounded-xl text-gray-500 hover:bg-gray-200 font-bold transition-colors">Cancelar</button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all transform hover:scale-105">Agendar
                    Cita</button>
            </div>
        </form>
    </div>

    <!-- Botón Flotante Fijo -->
    <button onclick="openManualCitaModal()" 
        class="fixed bottom-8 right-8 w-16 h-16 bg-blue-600 text-white rounded-full shadow-2xl shadow-blue-500/50 flex items-center justify-center hover:bg-blue-700 hover:scale-110 transition-all z-40 group"
        title="Nueva Cita Manual">
        <i class="fas fa-plus text-2xl group-hover:rotate-90 transition-transform"></i>
        <span class="absolute right-full mr-4 bg-gray-800 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none uppercase tracking-widest shadow-xl">
            Nueva Cita
        </span>
    </button>

@endsection

@push('scripts')
    <script>
        window.APP_CONFIG = {
            API_CITAS: "{{ route('panel.operaciones.citas.list') }}",
            API_UPDATE: "/panel/operaciones/citas",
            API_SEARCH_CLIENTS: "{{ route('panel.operaciones.citas.searchClients') }}",
            API_CHECK_CLIENT: "{{ route('panel.operaciones.citas.checkClientExists') }}",
            API_GET_VEHICLES: "/panel/operaciones/citas/api/get-client-vehicles",
            API_GET_BRANDS: "{{ route('panel.operaciones.citas.getBrands') }}",
            API_GET_MODELS: "/panel/mantenimientos/modelos/by-marca",
            API_GET_VERSIONS: "/panel/mantenimientos/versiones/by-modelo",
            API_CALENDAR_COUNTS: "{{ route('panel.operaciones.citas.getCalendarCounts') }}",
            CSRF_TOKEN: "{{ csrf_token() }}",
            USER_ROLES: {!! json_encode(Auth::user()->roles->pluck('slug')) !!},
            SUCURSALES_OPTIONS: {!! json_encode($sucursales->pluck('nombre', 'id')) !!},
            SUCURSAL_PHONES: {!! json_encode($sucursales->pluck('telefono', 'id')) !!}
        };
    </script>
    @vite('resources/js/panel/citas.js')
@endpush