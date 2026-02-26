<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'capacidad_bahias',
        'latitud',
        'longitud',
        'ciudad',
        'activa',
    ];

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
        'activa' => 'boolean',
        'capacidad_bahias' => 'integer',
    ];
}
