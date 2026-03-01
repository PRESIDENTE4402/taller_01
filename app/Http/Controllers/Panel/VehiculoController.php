<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehiculo;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class VehiculoController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehiculo::with(['cliente', 'marca', 'modelo', 'version']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $sucursalId = session('sucursal_id') ?? $user->sucursales->first()?->id;
            if ($sucursalId) {
                $query->whereHas('ordenes', function ($q) use ($sucursalId) {
                    $q->where('sucursal_id', $sucursalId);
                });
            } else {
                // Si no es admin y no tiene sucursal, no ve nada
                $query->whereRaw('0 = 1');
            }
        }

        if ($request->filled('buscar')) {
            $busqueda = $request->buscar;
            $query->where(function ($qq) use ($busqueda) {
                $qq->where('placa', 'like', "%{$busqueda}%")
                    ->orWhereHas('cliente', function ($q) use ($busqueda) {
                        $q->where('nombre_completo', 'like', "%{$busqueda}%")
                            ->orWhere('empresa', 'like', "%{$busqueda}%")
                            ->orWhere('nit', 'like', "%{$busqueda}%");
                    })
                    ->orWhereHas('marca', function ($q) use ($busqueda) {
                        $q->where('nombre', 'like', "%{$busqueda}%");
                    });
            });
        }

        $vehiculos = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('panel.vehiculos.index', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'marca_id' => 'required|exists:marcas_vehiculos,id',
            'modelo_id' => 'required|exists:modelos_vehiculos,id',
            'version_id' => 'nullable|exists:versiones_vehiculos,id',
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculos')],
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
        ]);

        // Fix: Ensure we don't save "null" string if passed
        if ($request->has('version_id') && $request->version_id === 'null') {
            $request->merge(['version_id' => null]);
        }

        $vehiculo = Vehiculo::create($request->all());
        $vehiculo->load(['marca', 'modelo', 'version', 'latestOrden']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vehículo registrado correctamente', 'vehiculo' => $vehiculo]);
        }

        return back()->with('success', 'Vehículo registrado correctamente');
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $request->validate([
            'marca_id' => 'required|exists:marcas_vehiculos,id',
            'modelo_id' => 'required|exists:modelos_vehiculos,id',
            'version_id' => 'nullable|exists:versiones_vehiculos,id',
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculos')->ignore($vehiculo->id)],
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        // Fix: Ensure we don't save "null" string if passed
        if ($request->has('version_id') && $request->version_id === 'null') {
            $request->merge(['version_id' => null]);
        }

        $vehiculo->update($request->all());
        $vehiculo->load(['marca', 'modelo', 'version', 'latestOrden']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vehículo actualizado correctamente', 'vehiculo' => $vehiculo]);
        }

        return back()->with('success', 'Vehículo actualizado correctamente');
    }

    public function show($id)
    {
        $vehiculo = Vehiculo::with([
            'cliente',
            'marca',
            'modelo',
            'version',
            'ordenes' => function ($query) {
                $query->orderBy('fecha_recepcion', 'desc');
            },
            'ordenes.detalles',
            'ordenes.archivos'
        ])->findOrFail($id);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $sucursalId = session('sucursal_id') ?? $user->sucursales->first()?->id;

            // Revisa si el vehículo tiene alguna orden en la sucursal del usuario
            $hasOrdenInSucursal = $vehiculo->ordenes->where('sucursal_id', $sucursalId)->count() > 0;

            if (!$hasOrdenInSucursal && $sucursalId) {
                abort(403, 'No tienes permiso para ver este vehículo porque no tiene historial en tu sucursal actual.');
            }
        }

        return view('panel.vehiculos.show', compact('vehiculo'));
    }

    public function destroy(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);

        // Prevent delete if has orders or appointments? 
        // Database foreign keys might handle this or we explicitly check
        if ($vehiculo->ordenes()->exists() || $vehiculo->citas()->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar el vehículo porque tiene historial.'], 422);
            }
            return back()->with('error', 'No se puede eliminar el vehículo porque tiene historial.');
        }

        $vehiculo->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vehículo eliminado correctamente']);
        }

        return back()->with('success', 'Vehículo eliminado correctamente');
    }
}
