@extends('layouts.panel')

@section('title', 'Gestión de Usuarios')
@section('subtitle', 'Asignación de roles y permisos a usuarios.')

@section('content')

    <div class="max-w-6xl mx-auto relative">
        {{-- Header Actions --}}
        <div class="flex flex-col sm:flex-row justify-between items-center gap-6 mb-8 relative z-10">
            <h2 class="text-2xl font-bold text-gray-800">Listado de Usuarios</h2>

            <div class="flex gap-4 w-full sm:w-auto">
                {{-- Search Bar --}}
                <div class="relative w-full sm:w-80group">
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-r from-blue-300 to-cyan-300 rounded-lg blur opacity-30 group-hover:opacity-75 transition duration-500">
                    </div>
                    <div class="relative flex items-center bg-white rounded-lg">
                        <i
                            class="fas fa-search absolute left-4 text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                        <input type="text" id="searchInput" placeholder=" "
                            class="w-full py-3 pl-12 pr-4 bg-transparent border-none focus:ring-0 text-gray-700 placeholder-transparent font-medium rounded-lg peer"
                            style="outline: none;">
                        <label for="searchInput"
                            class="absolute left-12 text-gray-400 text-sm duration-300 transform -translate-y-3 scale-75 top-1 z-10 origin-[0] peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-placeholder-shown:top-3 peer-focus:-translate-y-3 peer-focus:scale-75 peer-focus:top-1 peer-focus:text-blue-600">
                            Buscar usuario...
                        </label>
                    </div>
                </div>

                {{-- Create Button --}}
                <button onclick="openCreateModal()"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-900 text-white rounded-lg font-bold shadow-md hover:bg-black transition-all whitespace-nowrap">
                    <i class="fas fa-plus mr-2"></i> Nuevo
                </button>
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
                            <div class="group relative z-0 w-full mb-6 max-h-45">
                                <select id="roleSelect"
                                    class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236B7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E')] bg-no-repeat bg-[length:1.25em_1.25em] bg-[right_0.5rem_center] pr-10">
                                    <option value="">-- Seleccionar Rol --</option>
                                    {{-- Opciones inyectadas --}}
                                </select>
                                <label for="roleSelect"
                                    class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 scale-75 -translate-y-6">
                                    Rol Asignado
                                </label>
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

    {{-- Modal Crear Usuario (Personas) --}}
    <div id="createUserModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="createBackdrop"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 border border-gray-100"
                    id="createPanel">

                    {{-- Header Tech --}}
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-600 to-cyan-400"></div>

                    <div class="px-8 pt-8 pb-6">
                        <h3 class="text-xl font-bold leading-6 text-gray-900 flex items-center gap-2">
                            <i class="fas fa-user-plus text-cyan-500"></i>
                            <span>Nuevo Registro de Personal</span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">Crea un usuario y vincula sus datos personales.</p>

                        <form id="createUserForm" onsubmit="saveUser(event)" class="mt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Datos de Cuenta --}}
                                <div class="col-span-1 md:col-span-2">
                                    <h4 class="text-sm font-bold text-gray-900 border-b pb-1 mb-3">Datos de Cuenta</h4>
                                </div>

                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="text" name="name" id="newUserName" required placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="newUserName"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Nombre Usuario *
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="email" name="email" id="newUserEmail" required placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="newUserEmail"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Email (Login) *
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="password" name="password" id="newUserPassword" required minlength="8"
                                            placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="newUserPassword"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Contraseña *
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <select id="newUserRole"
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236B7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E')] bg-no-repeat bg-[length:1.25em_1.25em] bg-[right_0.5rem_center] pr-10">
                                            <option value="">-- Sin Rol --</option>
                                        </select>
                                        <label for="newUserRole"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 scale-75 -translate-y-6">
                                            Rol Inicial
                                        </label>
                                    </div>
                                </div>

                                {{-- Datos Personales --}}
                                <div class="col-span-1 md:col-span-2 mt-2">
                                    <h4 class="text-sm font-bold text-gray-900 border-b pb-1 mb-3">Datos Personales</h4>
                                </div>

                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="text" name="nombres" id="persNombres" required placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="persNombres"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Nombres *
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="text" name="apellidos" id="persApellidos" required placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="persApellidos"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Apellidos *
                                        </label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <div class="group relative z-0 w-full mb-6">
                                            <input type="number" name="edad" id="persEdad" min="18" placeholder=" "
                                                class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                            <label for="persEdad"
                                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                                Edad
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="group relative z-0 w-full mb-6">
                                            <select name="sexo" id="persSexo"
                                                class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3E%3Cpath stroke=\'%236B7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3E%3C/svg%3E')] bg-no-repeat bg-[length:1.25em_1.25em] bg-[right_0.5rem_center] pr-10">
                                                <option value="">Seleccionar</option>
                                                <option value="Masculino">Masculino</option>
                                                <option value="Femenino">Femenino</option>
                                            </select>
                                            <label for="persSexo"
                                                class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 scale-75 -translate-y-6">
                                                Sexo
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="text" name="telefono" id="persTelefono" placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="persTelefono"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Teléfono
                                        </label>
                                    </div>
                                </div>

                                <div class="col-span-1 md:col-span-2">
                                    <div class="group relative z-0 w-full mb-6">
                                        <input type="text" name="direccion" id="persDireccion" placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" />
                                        <label for="persDireccion"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Dirección
                                        </label>
                                    </div>
                                </div>

                                <div class="col-span-1 md:col-span-2">
                                    <div class="group relative z-0 w-full mb-6">
                                        <textarea name="cursos" id="persCursos" rows="2" placeholder=" "
                                            class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer"></textarea>
                                        <label for="persCursos"
                                            class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:left-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                                            Cursos / Certificaciones
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 mt-8">
                                <button type="button" onclick="closeCreateModal()"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                                <button type="submit"
                                    class="px-6 py-2 text-sm font-bold text-white bg-gray-900 rounded-lg hover:bg-black shadow-lg hover:shadow-cyan-500/20">Registrar
                                    Usuario</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.API_URL = "{{ route('panel.seguridad.usuarios.index') }}";
        window.CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/panel/usuarios.js'])
@endpush