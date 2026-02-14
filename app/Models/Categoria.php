<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class Categoria extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'categorias';

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'parent_id',
        'descripcion',
    ];

    /**
     * Relación: Jerarquía - Categoría padre
     */
    public function parent()
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    /**
     * Relación: Jerarquía - Categorías hijas
     */
    public function children()
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    /**
     * Relación: Repuestos que pertenecen a esta categoría
     */
    public function repuestos()
    {
        return $this->hasMany(Repuesto::class);
    }

    /**
     * Relación: Esquemas de atributos dinámicos para esta categoría
     */
    public function attributeSchemas()
    {
        return $this->hasMany(DynamicAttributeSchema::class)
                    ->orderBy('display_order', 'asc');
    }
}
