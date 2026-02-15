<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index()
    {
        return view('panel.operaciones.clientes.index');
    }

    public function list(Request $request)
    {
        $search = $request->get('search');

        $query = Cliente::withCount('vehiculos')
            ->orderBy('nombre_completo', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nit', 'like', "%{$search}%");
            });
        }

        $clientes = $query->paginate(15);

        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:20|unique:clientes,telefono',
            'email' => 'nullable|email|max:255',
            'nit' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'es_empresa' => 'boolean',
            'empresa' => 'nullable|required_if:es_empresa,true|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $cliente = Cliente::create($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado correctamente',
                'data' => $cliente
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $cliente = Cliente::findOrFail($id);

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => ['required', 'string', 'max:20', Rule::unique('clientes')->ignore($cliente->id)],
            'email' => 'nullable|email|max:255',
            'nit' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'es_empresa' => 'boolean',
            'empresa' => 'nullable|required_if:es_empresa,true|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $cliente->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'data' => $cliente
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cliente = Cliente::withCount(['vehiculos'])->findOrFail($id);

            // Validar si tiene dependencias importantes (citas, ordenes, vehiculos)
            // Si tiene vehículos, advertir o impedir. Por ahora impedimos si tiene vehículos.
            if ($cliente->vehiculos_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene vehículos asociados.'
                ], 422);
            }

            // También verificar Citas u Ordenes si existen relaciones directas que no pasen por vehículo (aunque vehiculo es el nexo principal)
            // Asumimos que si no tiene vehículos, es seguro borrarlo (o es un cliente nuevo sin historial)

            $cliente->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cliente: ' . $e->getMessage()
            ], 500);
        }
    }
}
