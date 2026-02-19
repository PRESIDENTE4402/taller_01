<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre_completo',
        'email',
        'telefono',
        'empresa',
        'nit',
        'direccion',
        'es_empresa',
        'situacion'
    ];

    protected $casts = [
        'es_empresa' => 'boolean',
    ];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function ordenes()
    {
        return $this->hasMany(OrdenTrabajo::class);
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
