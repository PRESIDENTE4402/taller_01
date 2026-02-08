@extends('layouts.panel')

@section('title', 'Control de Asistencias')
@section('subtitle', 'Escanear QR de empleados')

@section('content')
    {{-- CRITICAL STYLES: DO NOT REMOVE - Prevents white flash and fixes modal visibility --}}
    <style>
        /* Hide modal before JS loads */
        .hidden {
            display: none !important;
        }

        /* Force dark background immediately */
        body {
            background-color: #141517 !important;
            color: #e5e7eb;
        }

        /* Force Text Colors for Compatibility */
        .label-text {
            color: white !important;
        }

        .text-gray-400 {
            color: #9ca3af !important;
        }

        /* Inputs */
        input:not([type="radio"]),
        select,
        textarea {
            background-color: #2C2E33 !important;
            color: white !important;
            border-color: #4B5563 !important;
        }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-140px)]">

        <!-- Sección Izquierda: Scanner y Estado -->
        <div class="lg:col-span-1 flex flex-col gap-6">

            <!-- Scanner Card -->
            <div class="bg-[#1A1B1E] rounded-xl border border-gray-800 shadow-2xl p-4 flex flex-col relative group">
                <div
                    class="absolute inset-0 bg-blue-500/5 group-hover:bg-blue-500/10 transition duration-500 pointer-events-none">
                </div>

                <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-qrcode text-[#1C69D4]"></i> Escáner Biométrico
                </h3>

                @can('registrar_asistencia')
                    <!-- Viewport del Scanner -->
                    <div id="reader"
                        class="w-full bg-black rounded-lg overflow-hidden border-2 border-dashed border-gray-700 relative h-64">
                        <div class="absolute inset-0 flex items-center justify-center text-gray-500 z-0">
                            <i class="fas fa-camera text-4xl mb-2"></i>
                            <p class="text-xs">Esperando cámara...</p>
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <button id="btn-start-scanner"
                            class="btn btn-sm bg-[#1C69D4] hover:bg-blue-700 text-white flex-1 border-none font-bold uppercase tracking-wide">
                            <i class="fas fa-power-off mr-2"></i> Activar
                        </button>
                        <button id="btn-stop-scanner"
                            class="btn btn-sm bg-red-600 hover:bg-red-700 text-white flex-1 border-none font-bold uppercase tracking-wide hidden">
                            <i class="fas fa-stop-circle mr-2"></i> Detener
                        </button>
                    </div>

                    <!-- Manual Input fallback -->
                    <div class="mt-4 pt-4 border-t border-gray-800">
                        <p class="text-xs text-gray-400 mb-2 uppercase tracking-wide font-bold">Registro Manual por Nombre</p>
                        <div class="relative w-full">
                            <div class="flex gap-2">
                                <input type="text" id="manual-user-search" placeholder="Escribe el nombre..." autocomplete="off"
                                    style="background-color: #1f2937 !important; color: white !important;"
                                    class="input input-sm input-bordered w-full focus:border-[#1C69D4] placeholder-gray-400 font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500 border-gray-600">
                                <button id="btn-manual-search-icon"
                                    class="btn btn-sm btn-square bg-[#1C69D4] text-white border-none cursor-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>

                            <!-- Dropdown de Resultados -->
                            <ul id="search-results"
                                class="absolute z-50 left-0 right-0 bottom-full mb-2 bg-[#25262B] border border-gray-700 rounded-lg shadow-2xl max-h-48 overflow-y-auto hidden">
                                <!-- JS injections -->
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-800/50 rounded-lg p-6 text-center border border-gray-700">
                        <i class="fas fa-lock text-gray-600 text-3xl mb-3"></i>
                        <p class="text-gray-400 text-sm">No tienes permisos para registrar asistencias.</p>
                        <p class="text-[10px] text-gray-500 mt-2 uppercase">Requiere: registrar_asistencia</p>
                    </div>
                @endcan
            </div>

            <!-- Last Scan Status Card (Keep As Is) -->
            <div id="status-card"
                class="bg-[#1A1B1E] rounded-xl border border-gray-800 p-6 flex-1 flex flex-col items-center justify-center text-center relative opacity-50 pointer-events-none transition-all duration-300">
                <div class="avatar placeholder mb-4">
                    <div
                        class="bg-gray-800 text-gray-500 rounded-full w-24 h-24 ring ring-gray-700 ring-offset-base-100 ring-offset-2">
                        <span class="text-3xl"><i class="fas fa-user"></i></span>
                    </div>
                </div>

                <h2 id="scanned-name" class="text-2xl font-bold text-white mb-1">Esperando...</h2>
                <p id="scanned-id" class="text-sm text-gray-500 font-mono mb-4">ID: --</p>

                <div id="scan-result-badge" class="badge badge-lg p-4 font-bold uppercase tracking-wider mb-4 hidden">
                    --
                </div>

                <div id="scan-time" class="text-4xl font-mono text-white font-bold tracking-widest text-[#1C69D4]">
                    --:--
                </div>
            </div>

        </div>

        <!-- Sección Derecha: Historial del Día -->
        <div class="lg:col-span-2 bg-[#1A1B1E] rounded-xl border border-gray-800 shadow-xl flex flex-col overflow-hidden">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#25262B]">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-gray-400"></i> Registros de Hoy
                </h3>
                <div class="text-sm font-mono text-[#1C69D4]" id="live-clock">--:--:--</div>
            </div>

            <div class="flex-1 overflow-auto p-0">
                <table class="table table-zebra w-full text-left">
                    <thead
                        class="bg-[#2C2E33] text-white uppercase text-xs sticky top-0 z-10 font-bold border-b border-gray-600">
                        <tr>
                            <th class="py-3 px-4 text-white">Fecha</th>
                            <th class="py-3 px-4 text-white">Entrada</th>
                            <th class="py-3 px-4 text-white">Salida</th>
                            <th class="py-3 px-4 text-white">Empleado</th>
                            <th class="py-3 px-4 text-white">Tipo</th>
                            <th class="py-3 px-4 text-white">Estado</th>
                            <th class="py-3 px-4 text-white">Obs</th>
                            <th class="py-3 px-4 text-center text-white">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="live-table-body" class="text-sm text-gray-300 divide-y divide-gray-800">
                        <!-- JS fills this -->
                    </tbody>
                </table>
                <div id="empty-state" class="flex flex-col items-center justify-center h-full py-20 text-gray-600">
                    <i class="fas fa-inbox text-4xl mb-3 opacity-50"></i>
                    <p>Sin registros hoy</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Audio para feedback -->
    <audio id="scan-sound-success"
        src="https://assets.mixkit.co/sfx/preview/mixkit-software-interface-start-2574.mp3"></audio>
    <audio id="scan-sound-error" src="https://assets.mixkit.co/sfx/preview/mixkit-simple-game-countdown-921.mp3"></audio>

@endsection

{{-- MODAL DE CONFIRMACIÓN DE ASISTENCIA --}}
<div id="confirmModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity opacity-0" id="confirmBackdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-[#1A1B1E] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-gray-700"
                id="confirmPanel">

                <div class="px-6 py-6 pb-2">
                    <!-- Clásica cabecera con Nombre y Hora -->
                    <div class="flex justify-between items-center mb-6 border-b border-gray-700 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="avatar placeholder">
                                <div
                                    class="bg-blue-900 text-blue-200 rounded-full w-12 h-12 flex items-center justify-center">
                                    <span class="text-xl font-bold"><i class="fas fa-user"></i></span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white leading-tight" id="modalUserName">--</h3>
                                <div class="flex items-center gap-4 mt-2">
                                    <!-- Entrada -->
                                    <div class="flex flex-col">
                                        <label for="modalInputEntrada"
                                            class="text-[10px] text-gray-400 font-bold uppercase mb-1">Entrada</label>
                                        <input type="time" id="modalInputEntrada"
                                            style="background-color: #2C2E33 !important; color: white !important;"
                                            class="input input-xs bg-[#2C2E33] text-white border border-gray-600 focus:border-blue-500 rounded px-1 w-24">
                                    </div>
                                    <!-- Salida -->
                                    <div class="flex flex-col">
                                        <label for="modalInputSalida"
                                            class="text-[10px] text-gray-400 font-bold uppercase mb-1">Salida</label>
                                        <input type="time" id="modalInputSalida"
                                            style="background-color: #2C2E33 !important; color: white !important;"
                                            class="input input-xs bg-[#2C2E33] text-white border border-gray-600 focus:border-blue-500 rounded px-1 w-24">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="badgeLlegada" class="badge badge-lg font-bold uppercase tracking-wider p-4">--</div>
                    </div>

                    <form id="confirmForm" class="space-y-4">
                        <!-- Acción: Entrada / Salida -->
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer relative overflow-hidden group">
                                <input type="radio" name="accion" value="entrada" id="radioEntrada"
                                    class="peer sr-only">
                                <div
                                    class="p-4 rounded-xl border border-gray-700 bg-gray-800 hover:bg-gray-700 transition-all text-center text-white peer-checked:bg-[#1C69D4] peer-checked:border-[#1C69D4] peer-checked:text-white">
                                    <div class="text-xs uppercase tracking-widest mb-1 opacity-70">Marcar</div>
                                    <div class="text-xl font-bold"><i class="fas fa-sign-in-alt flex-col"></i> ENTRADA
                                    </div>
                                </div>
                            </label>

                            <label class="cursor-pointer relative overflow-hidden group">
                                <input type="radio" name="accion" value="salida" id="radioSalida" class="peer sr-only">
                                <div
                                    class="p-4 rounded-xl border border-gray-700 bg-gray-800 hover:bg-gray-700 transition-all text-center text-white peer-checked:bg-red-600 peer-checked:border-red-600 peer-checked:text-white">
                                    <div class="text-xs uppercase tracking-widest mb-1 opacity-70">Marcar</div>
                                    <div class="text-xl font-bold"><i class="fas fa-sign-out-alt"></i> SALIDA</div>
                                </div>
                            </label>
                        </div>

                        <!-- Detalles del Registro -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-[#1f2023] rounded-xl border border-gray-700">
                            <!-- Tipo de Asistencia -->
                            <div class="form-control">
                                <label class="label pt-0"><span
                                        class="label-text text-white font-bold uppercase text-xs">Tipo de
                                        Asistencia</span></label>
                                <select name="tipo_asistencia" id="selectTipoAsistencia"
                                    style="background-color: #2C2E33 !important; color: white !important; border-color: #4B5563;"
                                    class="select select-bordered select-sm w-full focus:border-blue-500 rounded-lg">
                                    <option value="presente">Presente</option>
                                    <option value="ausente">Ausente</option>
                                    <option value="permiso">Permiso / Comisión</option>
                                    <option value="vacaciones">Vacaciones</option>
                                    <option value="suspension_medica">Suspensión Médica</option>
                                </select>
                            </div>

                            <!-- Estado del Registro -->
                            <div class="form-control">
                                <label class="label pt-0"><span
                                        class="label-text text-white font-bold uppercase text-xs">Estado del
                                        Registro</span></label>
                                <select name="estado" id="selectEstado"
                                    style="background-color: #2C2E33 !important; color: white !important; border-color: #4B5563;"
                                    class="select select-bordered select-sm w-full focus:border-blue-500 rounded-lg">
                                    <option value="a_tiempo">A Tiempo</option>
                                    <option value="tardanza">Tardanza</option>
                                    <option value="falta_justificada">Falta Justificada</option>
                                    <option value="falta_injustificada">Falta Injustificada</option>
                                </select>
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-control">
                            <label class="label"><span
                                    class="label-text text-white text-xs uppercase font-bold">Observaciones</span></label>
                            <textarea id="modalObservaciones" name="observaciones"
                                style="background-color: #2C2E33 !important; color: white !important; border-color: #4B5563;"
                                class="textarea textarea-bordered h-20 focus:border-blue-500 w-full resize-none rounded-lg"
                                placeholder="Añadir nota opcional..."></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="modal-action pt-2 flex justify-end gap-3">
                            <button type="button" onclick="closeConfirmModal()"
                                style="color: white !important; background-color: #4B5563 !important;"
                                class="btn bg-gray-600 hover:bg-gray-700 text-white border-none">Cancelar</button>
                            <button type="submit"
                                class="btn bg-[#1C69D4] hover:bg-blue-700 text-white gap-2 border-0 px-8">
                                <i class="fas fa-check-circle"></i> Confirmar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    {{-- Librarian HTML5-QRCode --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    @vite(['resources/js/panel/asistencia.js'])
@endpush