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

                    {{-- Barra Superior Decorativa --}}
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-cyan-400"></div>

                    <div class="px-8 pt-8 pb-6">
                        <div class="text-center sm:text-left">
                            <h3 class="text-xl font-bold leading-6 text-gray-900 flex items-center gap-2" id="modalTitle">
                                <i class="fas fa-user-shield text-cyan-500"></i>
                                <span>Nuevo Rol</span>
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Define los permisos y alcances del rol.</p>

                            <div class="mt-6">
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

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Permisos
                                            Asignados</label>
                                        <div id="permissionsCheckboxList"
                                            class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-3 border rounded-lg bg-gray-50 border-gray-200">
                                            {{-- Inyectado por JS --}}
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50/80 px-8 py-4 sm:flex sm:flex-row-reverse gap-3 border-t border-gray-100 rounded-b-2xl">
                        <button type="button"
                            onclick="document.getElementById('roleForm').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))"
                            class="inline-flex w-full justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:shadow-cyan-500/30 hover:bg-black transition-all sm:w-auto">Guardar</button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-5 py-2.5 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "{{ route('panel.seguridad.roles.index') }}";
        const PERMISSIONS_API_URL = "{{ route('panel.seguridad.permisos.index') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/panel/roles.js'])
@endpush