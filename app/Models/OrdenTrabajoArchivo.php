<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrdenTrabajoArchivo extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function ordenTrabajo()
    {
        return $this->belongsTo(OrdenTrabajo::class);
    }
}
