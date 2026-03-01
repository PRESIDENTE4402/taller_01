<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandingImage extends Model
{
    use HasFactory;

    protected $table = 'landing_images';

    protected $fillable = [
        'type',
        'title',
        'description',
        'image_url',
        'cloudinary_public_id',
        'alt_text',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Métodos útiles
    public function getTypeLabel()
    {
        return match ($this->type) {
            'logo' => 'Logo Empresarial',
            'service' => 'Imagen de Servicio',
            'gallery' => 'Galería de Trabajos',
            'video' => 'Video de Éxito',
            'about' => 'Imagen Acerca De',
            default => 'Otro'
        };
    }
}
