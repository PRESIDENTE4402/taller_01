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
        'password',
        'telefono',
        'empresa',
        'nit',
        'direccion',
        'es_empresa',
        'situacion'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'es_empresa' => 'boolean',
    ];

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
        }
    }

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
