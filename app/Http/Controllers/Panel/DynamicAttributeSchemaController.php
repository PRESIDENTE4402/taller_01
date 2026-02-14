<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\DynamicAttributeSchema;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DynamicAttributeSchemaController extends Controller
{
    /**
     * Obtener todos los atributos dinámicos de una categoría
     */
    public function listByCategoria($categoriaId)
    {
        try {
            // Validar que el usuario tiene acceso a esta categoría
            $categoria = Categoria::forCurrentUser()
                ->findOrFail($categoriaId);

            $schemas = DynamicAttributeSchema::where('categoria_id', $categoriaId)
                ->orderBy('display_order', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $schemas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Atributos no encontrados'
            ], 404);
        }
    }

    /**
     * Crear un nuevo atributo dinámico
     */
    public function store(Request $request)
    {
        $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'attribute_name' => 'required|string|max:255',
            'data_type' => 'required|in:text,number,date,select,boolean',
            'is_required' => 'boolean',
            'options' => 'nullable|array',
            'display_order' => 'nullable|integer',
            // Validación avanzada
            'regex_pattern' => 'nullable|string',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'help_text' => 'nullable|string|max:1000',
            // Traducciones
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required|string|size:2',
            'translations.*.label' => 'required|string|max:255',
            'translations.*.description' => 'nullable|string|max:500',
        ]);

        try {
            // Validar que el usuario tiene acceso a esta categoría
            $categoria = Categoria::forCurrentUser()
                ->findOrFail($request->categoria_id);

            // Validar que max_length >= min_length
            if ($request->min_length && $request->max_length && $request->min_length > $request->max_length) {
                throw new \Exception("Longitud mínima no puede ser mayor que máxima");
            }

            // Validar que max_value >= min_value
            if ($request->min_value && $request->max_value && $request->min_value > $request->max_value) {
                throw new \Exception("Valor mínimo no puede ser mayor que máximo");
            }

            $schema = DynamicAttributeSchema::create([
                'sucursal_id' => $categoria->sucursal_id,
                'categoria_id' => $request->categoria_id,
                'attribute_name' => $request->attribute_name,
                'data_type' => $request->data_type,
                'is_required' => $request->is_required ?? false,
                'options' => $request->options,
                'display_order' => $request->display_order ?? 0,
                'regex_pattern' => $request->regex_pattern,
                'min_length' => $request->min_length,
                'max_length' => $request->max_length,
                'min_value' => $request->min_value,
                'max_value' => $request->max_value,
                'help_text' => $request->help_text,
                'is_active' => true,
            ]);

            // Agregar traducciones si existen
            if ($request->translations) {
                foreach ($request->translations as $translation) {
                    $schema->translations()->create([
                        'locale' => $translation['locale'],
                        'label' => $translation['label'],
                        'description' => $translation['description'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Atributo creado exitosamente.',
                'data' => $schema->load('translations')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Actualizar un atributo dinámico
     */
    public function update(Request $request, $id)
    {
        $schema = DynamicAttributeSchema::findOrFail($id);

        // Validar que el usuario tiene acceso
        if (!Auth::user()->sucursales->contains($schema->sucursal_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado'
            ], 403);
        }

        $request->validate([
            'attribute_name' => 'required|string|max:255',
            'data_type' => 'required|in:text,number,date,select,boolean',
            'is_required' => 'boolean',
            'options' => 'nullable|array',
            'display_order' => 'nullable|integer',
        ]);

        try {
            $schema->update($request->only([
                'attribute_name',
                'data_type',
                'is_required',
                'options',
                'display_order'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Atributo actualizado exitosamente.',
                'data' => $schema
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Eliminar un atributo dinámico
     */
    public function destroy($id)
    {
        $schema = DynamicAttributeSchema::findOrFail($id);

        // Validar que el usuario tiene acceso
        if (!Auth::user()->sucursales->contains($schema->sucursal_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado'
            ], 403);
        }

        try {
            $schema->delete();

            return response()->json([
                'success' => true,
                'message' => 'Atributo eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Reordenar atributos
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:dynamic_attribute_schemas,id',
            'items.*.display_order' => 'required|integer',
        ]);

        try {
            foreach ($request->items as $item) {
                $schema = DynamicAttributeSchema::findOrFail($item['id']);

                // Validar que el usuario tiene acceso
                if (!Auth::user()->sucursales->contains($schema->sucursal_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Acceso denegado'
                    ], 403);
                }

                $schema->update(['display_order' => $item['display_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Atributos reordenados exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
