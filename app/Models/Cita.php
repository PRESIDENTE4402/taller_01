<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class Cita extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'citas';

    protected $fillable = [
        'sucursal_id',
        'cliente_id',
        'vehiculo_id',
        'fecha_programada',
        'fecha_realizacion',
        'motivo_cita',
        'origen',
        'estado',
        'notas_secretario'
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_realizacion' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function ordenTrabajo()
    {
        return $this->hasOne(OrdenTrabajo::class, 'cita_id');
    }
}
