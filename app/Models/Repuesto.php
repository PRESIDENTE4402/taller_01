<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class Repuesto extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'repuestos';

    protected $fillable = [
        'sucursal_id',
        'categoria_id',
        'codigo_interno',
        'nombre',
        'marca_repuesto',
        'unidad_medida',
        'contenido_por_unidad',
        'precio_costo',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'ubicacion_estante',
        'atributos',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'atributos' => 'array',
        'precio_costo' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'contenido_por_unidad' => 'decimal:2',
    ];

    /**
     * Relación con Categoria
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Validar atributos dinámicos según el schema de la categoría
     * @throws \Exception
     */
    public static function validateAttributes($categoriaId, $atributos)
    {
        if (empty($atributos)) {
            return true;
        }

        $schemas = DynamicAttributeSchema::where('categoria_id', $categoriaId)
            ->where('is_required', true)
            ->get();

        foreach ($schemas as $schema) {
            if (!isset($atributos[$schema->attribute_name]) || empty($atributos[$schema->attribute_name])) {
                throw new \Exception("Falta atributo requerido: {$schema->attribute_name}");
            }
        }

        return true;
    }

    /**
     * Obtener atributos dinámicos para mostrar
     */
    public function getAttributeSchemas()
    {
        return DynamicAttributeSchema::where('categoria_id', $this->categoria_id)
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Relación con Movimientos
     */
    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class)->orderBy('created_at', 'desc');
    }
}
