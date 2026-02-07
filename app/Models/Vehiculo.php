<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'cliente_id',
        'marca_id',
        'modelo_id',
        'version_id',
        'placa',
        'anio',
        'vin'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function marca()
    {
        return $this->belongsTo(MarcaVehiculo::class);
    }

    public function modelo()
    {
        return $this->belongsTo(ModeloVehiculo::class);
    }

    public function version()
    {
        return $this->belongsTo(VersionVehiculo::class);
    }
}
