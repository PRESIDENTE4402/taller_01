@extends('layouts.panel')

@section('title', 'Nueva Orden de Trabajo')
@section('subtitle', 'Registra la recepción del vehículo y diagnóstico inicial')

@section('content')
<form action="{{ route('panel.operaciones.ordenes_trabajo.store') }}" method="POST" id="ordenForm" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    @csrf
    
    <!-- Left Column: Identificación -->
    <div class="lg:col-span-1 space-y-6">
        <!-- Cliente Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-user-circle text-blue-500"></i> Cliente
            </h3>
            
            @if(isset($cliente))
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-2">
                    <p class="font-bold text-gray-800">{{ $cliente->nombre_completo }}</p>
                    <p class="text-sm text-gray-600">{{ $cliente->telefono }}</p>
                    <p class="text-xs text-gray-400">{{ $cliente->email }}</p>
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
                </div>
            @else
                <div class="relative">
                    <input type="text" placeholder="Buscar Cliente..." class="input w-full border-gray-300 rounded-lg">
                    <!-- TODO: Implement Client Search if not from Cita -->
                </div>
            @endif
        </div>

        <!-- Vehículo Card -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-car text-blue-500"></i> Vehículo
            </h3>

            @if(isset($vehiculo))
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-gray-800 text-lg">{{ $vehiculo->placa }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $vehiculo->marca->nombre }} {{ $vehiculo->modelo->nombre }} {{ $vehiculo->anio }}
                            </p>
                        </div>
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <input type="hidden" name="vehiculo_id" value="{{ $vehiculo->id }}">
                </div>
            @else
                <p>Seleccione un cliente primero</p>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-3">
                <div>
                    <label class="label-text text-xs uppercase font-bold text-gray-400">Kilometraje Act.</label>
                    <input type="number" name="kilometraje" class="input w-full border-gray-300 rounded-lg focus:ring-blue-500 font-mono font-bold" required placeholder="000000">
                </div>
                 <div>
                    <label class="label-text text-xs uppercase font-bold text-gray-400">Nivel Combustible</label>
                    <select name="nivel_combustible" class="input w-full border-gray-300 rounded-lg focus:ring-blue-500">
                        <option value="R">Reserva (E)</option>
                        <option value="1/4">1/4 Tanque</option>
                        <option value="1/2" selected>1/2 Tanque</option>
                        <option value="3/4">3/4 Tanque</option>
                        <option value="F">Full (F)</option>
                    </select>
                </div>
                <div>
                     <label class="label-text text-xs uppercase font-bold text-gray-400">Color</label>
                     <input type="text" name="color" class="input w-full border-gray-300 rounded-lg focus:ring-blue-500" required placeholder="Ej: Rojo">
                </div>
            </div>
        </div>

        @if(isset($cita))
            <input type="hidden" name="cita_id" value="{{ $cita->id }}">
            <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100 text-sm text-yellow-800">
                <p class="font-bold mb-1"><i class="fas fa-info-circle"></i> Nota de Cita:</p>
                {{ $cita->motivo_cita }}
            </div>
        @endif
    </div>

    <!-- Center & Right: Detalles Orden -->
    <div class="lg:col-span-2 space-y-6 pb-20">
        
        <!-- Falla Reportada -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Falla o Servicio Solicitado</h3>
            <textarea name="falla_cliente" rows="4" class="w-full border-gray-200 rounded-xl p-4 focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Describa detalladamente el problema reportado por el cliente..." required>{{ isset($cita) ? $cita->motivo_cita : '' }}</textarea>
        </div>

        <!-- Inventario / Checklist (Simulada) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">Inventario de Recepción</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Tarjeta Circulación" class="checkbox checkbox-sm checkbox-primary rounded">
                    <span class="text-sm text-gray-700">Tarjeta Circulación</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Llanta Repuesto" class="checkbox checkbox-sm checkbox-primary rounded">
                    <span class="text-sm text-gray-700">Llanta Repuesto</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Gata" class="checkbox checkbox-sm checkbox-primary rounded">
                    <span class="text-sm text-gray-700">Gata</span>
                </label>
                 <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Llave Ruedas" class="checkbox checkbox-sm checkbox-primary rounded">
                    <span class="text-sm text-gray-700">Llave Ruedas</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Radio/Frontal" class="checkbox checkbox-sm checkbox-primary rounded">
                    <span class="text-sm text-gray-700">Radio/Frontal</span>
                </label>
                 <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Encendedor" class="checkbox checkbox-sm checkbox-primary rounded">
                    <span class="text-sm text-gray-700">Encendedor</span>
                </label>
                 <label class="flex items-center gap-2 cursor-pointer bg-gray-50 p-3 rounded-lg hover:bg-gray-100">
                    <input type="checkbox" name="inventario[]" value="Alfombras" class="checkbox checkbox-sm checkbox-primary rounded" checked>
                    <span class="text-sm text-gray-700">Alfombras</span>
                </label>
            </div>
        </div>

        <!-- Damage Canvas (Placeholder for now) -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
             <h3 class="font-bold text-gray-800 mb-4">Daños Visuales (Exterior)</h3>
             <div class="bg-gray-100 rounded-xl h-64 flex items-center justify-center border-2 border-dashed border-gray-300">
                 <p class="text-gray-400 font-medium">Aquí iría el diagrama del vehículo interactivo (Canvas)</p>
             </div>
        </div>

    </div>

    <!-- Floating Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 lg:pl-80 flex justify-end gap-4 z-50 shadow-upper">
        <a href="{{ route('panel.operaciones.ordenes_trabajo.index') }}" class="btn bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-6 rounded-xl">Cancelar</a>
        <button type="submit" class="btn bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold px-8 rounded-xl shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 transform hover:-translate-y-0.5 transition-all">
            <i class="fas fa-save mr-2"></i> Generar Orden de Trabajo
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('ordenForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Procesando...';
        btn.disabled = true;

        try {
            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                Swal.fire({
                    title: 'Orden Creada',
                    text: 'La orden de trabajo se generó correctamente.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = result.redirect;
                });
            } else {
                Swal.fire('Error', result.message || 'Error desconocido', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Error de conexión', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
</script>
<style>
    .shadow-upper {
        box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>
@endpush
