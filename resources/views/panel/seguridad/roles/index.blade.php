@extends('layouts.panel')

@section('title', 'Gestión de Roles')
@section('subtitle', 'Administración de roles y permisos del sistema.')

@section('content')

    <div class="max-w-4xl mx-auto relative">
        {{-- Header Actions --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">
            <h2 class="text-2xl font-bold text-gray-800">Listado de Roles</h2>

            <button onclick="openModal()"
                class="relative inline-flex items-center justify-center px-6 py-2 overflow-hidden font-bold text-white transition-all duration-300 bg-blue-600 rounded-lg group hover:scale-105 shadow-md hover:bg-blue-700">
                <span class="mr-2"><i class="fas fa-plus"></i></span> Nuevo Rol
            </button>
        </div>

        {{-- Grid de Roles --}}
        <div id="rolesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Inyectado por JS --}}
        </div>

        {{-- Empty State --}}
        <div id="emptyState" class="hidden flex-col items-center justify-center py-16 text-center text-gray-400">
            <i class="fas fa-user-shield text-4xl mb-3"></i>
            <p>No hay roles definidos.</p>
        </div>
    </div>

    {{-- Modal --}}
    <div id="roleModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-gray-100"
                    id="modalPanel">

                    <div class="px-8 pt-8 pb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-1" id="modalTitle">Nuevo Rol</h3>
                        <p class="text-sm text-gray-500 mb-6">Define el nombre del rol.</p>

                        <form id="roleForm" onsubmit="saveRole(event)">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Rol</label>
                                <input type="text" id="roleName"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ej. Administrador" required>
                                <span class="text-xs text-red-500 hidden" id="errorName"></span>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                                <textarea id="roleDesc" rows="3"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Describe los permisos o el propósito de este rol."></textarea>
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" onclick="closeModal()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "{{ route('panel.seguridad.roles.index') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/panel/roles.js'])
@endpush