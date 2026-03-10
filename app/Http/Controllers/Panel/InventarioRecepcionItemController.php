<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\InventarioRecepcionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventarioRecepcionItemController extends Controller
{
    public function index()
    {
        return view('panel.mantenimientos.inventario_recepcion.index');
    }

    public function list()
    {
        $items = InventarioRecepcionItem::orderBy('seccion')->orderBy('orden')->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'seccion' => 'required|in:documentos_accesorios,herramientas_exterior',
            'tipo' => 'required|in:si_no,cantidad,tapiceria,documentos',
            'orden' => 'nullable|integer',
        ]);

        $item = InventarioRecepcionItem::create([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre, '_'),
            'tipo' => $request->tipo,
            'seccion' => $request->seccion,
            'orden' => $request->orden ?? 0,
            'activo' => true,
        ]);

        return response()->json(['success' => true, 'message' => 'Ítem creado correctamente', 'item' => $item]);
    }

    public function update(Request $request, $id)
    {
        $item = InventarioRecepcionItem::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'seccion' => 'required|in:documentos_accesorios,herramientas_exterior',
            'tipo' => 'required|in:si_no,cantidad,tapiceria,documentos',
            'orden' => 'nullable|integer',
            'activo' => 'required|boolean',
        ]);

        $item->update([
            'nombre' => $request->nombre,
            'slug' => Str::slug($request->nombre, '_'),
            'tipo' => $request->tipo,
            'seccion' => $request->seccion,
            'orden' => $request->orden ?? 0,
            'activo' => $request->activo,
        ]);

        return response()->json(['success' => true, 'message' => 'Ítem actualizado correctamente']);
    }

    public function destroy($id)
    {
        $item = InventarioRecepcionItem::findOrFail($id);
        $item->delete();

        return response()->json(['success' => true, 'message' => 'Ítem eliminado correctamente']);
    }
}
