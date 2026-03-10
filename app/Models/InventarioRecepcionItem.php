<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioRecepcionItem extends Model
{
    use HasFactory;

    protected $table = 'inventario_recepcion_items';

    protected $fillable = [
        'nombre',
        'slug',
        'tipo',
        'seccion',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];
}
