<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class Cliente extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'clientes';

    protected $fillable = [
        'sucursal_id',
        'nombre_completo',
        'email',
        'telefono',
        'empresa',
        'nit',
        'direccion',
        'es_empresa'
    ];

    protected $casts = [
        'es_empresa' => 'boolean',
    ];

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }
}
