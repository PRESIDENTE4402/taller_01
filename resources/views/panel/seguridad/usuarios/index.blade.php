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
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity opacity-100" id="createBackdrop">
        </div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <div class="relative transform overflow-hidden rounded-2xl bg-[#fcfcfc] text-left shadow-[0_20px_50px_rgba(0,0,0,0.3)] transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-200"
                    id="createPanel">

                    {{-- Acento de color: "Electric Blue" para Tesla y Silver para Euro --}}
                    <div
                        class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-700 via-cyan-400 to-slate-800">
                    </div>

                    <div class="px-8 pt-10 pb-8">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <h3 class="text-2xl font-black tracking-tight text-slate-900 uppercase">
                                    <i class="fas fa-bolt text-blue-600 mr-2"></i> <span class="text-blue-600">_</span>
                                </h3>
                                <p class="text-xs font-medium text-slate-500 tracking-widest uppercase mt-1">Specialized
                                    Tech Division</p>
                            </div>
                            <i class="fas fa-microchip text-3xl text-slate-200"></i>
                        </div>

                        <form id="createUserForm" onsubmit="saveUser(event)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                                {{-- Sección: Datos de Cuenta --}}
                                <div class="col-span-1 md:col-span-2">
                                    <h4
                                        class="text-[10px] font-bold text-blue-600 tracking-[0.2em] uppercase border-b border-blue-100 pb-1 mb-2">
                                        Acceso al Sistema</h4>
                                </div>

                                <div class="relative">
                                    <label for="newUserName"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Nombre
                                        de Usuario *</label>
                                    <input type="text" id="newUserName" name="name" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none placeholder:text-slate-400"
                                        placeholder="ej. j.doe_tesla">
                                </div>

                                <div class="relative">
                                    <label for="newUserEmail"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Email
                                        (Login) *</label>
                                    <input type="email" id="newUserEmail" name="email" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none"
                                        placeholder="staff@workshop.com">
                                </div>

                                <div class="relative">
                                    <label for="newUserPassword"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Contraseña
                                        *</label>
                                    <input type="password" id="newUserPassword" name="password" required minlength="8"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none"
                                        placeholder="••••••••">
                                </div>

                                <div class="relative">
                                    <label for="newUserRole"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Rol
                                        de Sistema</label>
                                    <select id="newUserRole"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none appearance-none cursor-pointer">
                                        <option value="">-- Seleccionar Nivel --</option>

                                    </select>
                                </div>

                                {{-- Sección: Datos Personales --}}
                                <div class="col-span-1 md:col-span-2 mt-4">
                                    <h4
                                        class="text-[10px] font-bold text-blue-600 tracking-[0.2em] uppercase border-b border-blue-100 pb-1 mb-2">
                                        Información del Personal</h4>
                                </div>

                                <div class="relative">
                                    <label for="persNombres"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Nombres
                                        *</label>
                                    <input type="text" id="persNombres" name="nombres" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none"
                                        placeholder="Nombre completo">
                                </div>

                                <div class="relative">
                                    <label for="persApellidos"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Apellidos
                                        *</label>
                                    <input type="text" id="persApellidos" name="apellidos" required
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none"
                                        placeholder="Apellidos completos">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="persEdad"
                                            class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Edad</label>
                                        <input type="number" id="persEdad" name="edad" min="18"
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none">
                                    </div>
                                    <div>
                                        <label for="persSexo"
                                            class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Género</label>
                                        <select id="persSexo" name="sexo"
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none appearance-none cursor-pointer">
                                            <option value="">N/A</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="relative">
                                    <label for="persTelefono"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Teléfono
                                        de Contacto</label>
                                    <input type="text" id="persTelefono" name="telefono"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none"
                                        placeholder="+1 234 567 890">
                                </div>

                                <div class="col-span-1 md:col-span-2">
                                    <label for="persDireccion"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Residencia</label>
                                    <input type="text" id="persDireccion" name="direccion"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none"
                                        placeholder="Calle, Ciudad, Código Postal">
                                </div>

                                <div class="col-span-1 md:col-span-2">
                                    <label for="persCursos"
                                        class="block mb-1.5 text-xs font-bold text-slate-700 uppercase tracking-wider">Certificaciones
                                        (Tesla / BMW / Audi)</label>
                                    <textarea id="persCursos" name="cursos" rows="2"
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm transition-all focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none resize-none"
                                        placeholder="Listado de certificaciones técnicas..."></textarea>
                                </div>
                            </div>

                            {{-- Botones de Acción --}}
                            <div class="flex justify-end gap-4 mt-10">
                                <button type="button" onclick="closeCreateModal()"
                                    class="px-6 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors uppercase tracking-[0.2em]">
                                    Cancelar
                                </button>
                                <button type="submit"
                                    class="px-8 py-2.5 text-xs font-bold text-white bg-blue-700 hover:bg-blue-600 rounded-full shadow-[0_4px_15px_rgba(29,78,216,0.3)] transition-all hover:-translate-y-0.5 active:scale-95 uppercase tracking-[0.2em]">
                                    Guardar Registro
                                </button>
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