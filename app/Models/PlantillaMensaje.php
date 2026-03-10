<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaMensaje extends Model
{
    protected $table = 'plantillas_mensajes';

    protected $fillable = [
        'nombre',
        'slug',
        'asunto',
        'cuerpo',
        'tipo_canal',
        'categoria',
        'activo'
    ];

    /**
     * Remplaza los placeholders por valores reales.
     */
    public function parse($data)
    {
        $parsed = $this->cuerpo;
        foreach ($data as $key => $value) {
            $parsed = str_replace('{' . $key . '}', $value ?? '', $parsed);
        }
        return $parsed;
    }

    public function parseAsunto($data)
    {
        if (!$this->asunto)
            return '';
        $parsed = $this->asunto;
        foreach ($data as $key => $value) {
            $parsed = str_replace('{' . $key . '}', $value ?? '', $parsed);
        }
        return $parsed;
    }
}
