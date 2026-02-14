<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicAttributeTranslation extends Model
{
    use HasFactory;

    protected $table = 'dynamic_attribute_translations';

    protected $fillable = [
        'dynamic_attribute_schema_id',
        'locale',
        'label',
        'description',
    ];

    /**
     * Relación: Pertenece a un DynamicAttributeSchema
     */
    public function attributeSchema()
    {
        return $this->belongsTo(DynamicAttributeSchema::class, 'dynamic_attribute_schema_id');
    }

    /**
     * Scope: Obtener traducciones por idioma
     */
    public function scopeByLocale($query, $locale = 'es')
    {
        return $query->where('locale', $locale);
    }

    /**
     * Idiomas disponibles
     */
    public static function getAvailableLocales()
    {
        return [
            'es' => 'Español',
            'en' => 'English',
            'fr' => 'Français',
            'pt' => 'Português',
            'de' => 'Deutsch',
            'it' => 'Italiano',
        ];
    }
}
