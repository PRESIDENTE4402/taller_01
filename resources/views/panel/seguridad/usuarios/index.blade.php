@extends('layouts.panel')

@section('title', 'Gestión de Usuarios')
@section('subtitle', 'Asignación de roles y permisos a usuarios.')

@section('content')

    <div class="max-w-6xl mx-auto relative">
        {{-- Header Actions --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">
            <h2 class="text-2xl font-bold text-gray-800">Listado de Usuarios</h2>

            {{-- Search Bar --}}
            <div class="relative w-full sm:w-80">
                <div class="relative flex items-center bg-white rounded-lg border border-gray-200 shadow-sm">
                    <i class="fas fa-search absolute left-4 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario..."
                        class="w-full py-2.5 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400 rounded-lg outline-none">
                </div>
            </div>
        </div>

        {{-- Grid de Usuarios --}}
        <div id="usersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Inyectado por JS --}}
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center text-gray-400">
            <i class="fas fa-users-slash text-4xl mb-3"></i>
            <p>No se encontraron usuarios.</p>
        </div>
    </div>

    {{-- Modal Asignar Rol --}}
    <div id="assignRoleModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-gray-100"
                    id="modalPanel">

                    <div class="px-8 pt-8 pb-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="h-10 w-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-lg"
                                id="modalUserInitial">
                                U
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900" id="modalUserName">Usuario</h3>
                                <p class="text-xs text-gray-500" id="modalUserEmail">user@example.com</p>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mb-6 mt-2">Selecciona el rol principal para este usuario.</p>

                        <form id="assignRoleForm" onsubmit="saveAssignment(event)">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rol Asignado</label>
                                <select id="roleSelect"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Seleccionar Rol --</option>
                                    {{-- Opciones inyectadas --}}
                                </select>
                                <span class="text-xs text-red-500 hidden" id="errorRole"></span>
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" onclick="closeModal()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Guardar
                                    Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "{{ route('panel.seguridad.usuarios.index') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/panel/usuarios.js'])
@endpush