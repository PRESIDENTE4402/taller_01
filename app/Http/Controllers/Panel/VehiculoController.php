<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehiculo;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'marca_id' => 'required|exists:marcas_vehiculos,id',
            'modelo_id' => 'required|exists:modelos_vehiculos,id',
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculos')->whereNull('deleted_at')], // Assuming soft deletes or just unique
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
        ]);

        $vehiculo = Vehiculo::create($request->all());

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
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculos')->ignore($vehiculo->id)->whereNull('deleted_at')],
            'anio' => 'required|integer|min:1900|max:' . (date('Y') + 1),
        ]);

        $vehiculo->update($request->all());

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Vehículo actualizado correctamente', 'vehiculo' => $vehiculo]);
        }

        return back()->with('success', 'Vehículo actualizado correctamente');
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
