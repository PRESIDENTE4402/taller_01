<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSucursal;

class DynamicAttributeSchema extends Model
{
    use HasFactory, BelongsToSucursal;

    protected $table = 'dynamic_attribute_schemas';

    protected $fillable = [
        'sucursal_id',
        'categoria_id',
        'attribute_name',
        'data_type',
        'is_required',
        'options',
        'display_order',
        // Validación avanzada
        'regex_pattern',
        'min_length',
        'max_length',
        'min_value',
        'max_value',
        'help_text',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'min_length' => 'integer',
        'max_length' => 'integer',
        'min_value' => 'decimal:2',
        'max_value' => 'decimal:2',
    ];

    /**
     * Relación: Pertenece a una Categoría
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Relación: Traducciones multiidioma
     */
    public function translations()
    {
        return $this->hasMany(DynamicAttributeTranslation::class);
    }

    /**
     * Obtener traducción para idioma específico
     */
    public function getTranslation($locale = 'es')
    {
        return $this->translations()
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Scope: Obtener atributos de una categoría específica
     */
    public function scopeForCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId)
                     ->orderBy('display_order', 'asc');
    }

    /**
     * Obtener atributos como validación para Repuestos (MEJORADO CON VALIDACIONES AVANZADAS)
     */
    public static function getValidationRules($categoriaId)
    {
        $schemas = static::forCategoria($categoriaId)->where('is_active', true)->get();
        $rules = [];

        foreach ($schemas as $schema) {
            $baseRule = $schema->data_type === 'number' ? 'numeric' : 'string';
            $ruleArray = [];

            // Requerido o nullable
            if ($schema->is_required) {
                $ruleArray[] = 'required';
            } else {
                $ruleArray[] = 'nullable';
            }

            // Tipo base
            if ($schema->data_type !== 'number') {
                $ruleArray[] = $baseRule;
            }

            // Validaciones de longitud (strings)
            if ($schema->min_length) {
                $ruleArray[] = "min:{$schema->min_length}";
            }
            if ($schema->max_length) {
                $ruleArray[] = "max:{$schema->max_length}";
            }

            // Validaciones numéricas
            if ($schema->data_type === 'number') {
                if ($schema->min_value !== null) {
                    $ruleArray[] = "min:{$schema->min_value}";
                }
                if ($schema->max_value !== null) {
                    $ruleArray[] = "max:{$schema->max_value}";
                }
            }

            // Patrón regex
            if ($schema->regex_pattern) {
                $ruleArray[] = "regex:{$schema->regex_pattern}";
            }

            // Para selects, validar contra opciones
            if ($schema->data_type === 'select' && $schema->options) {
                $options = implode(',', $schema->options);
                $ruleArray[] = "in:{$options}";
            }

            // Para booleanos
            if ($schema->data_type === 'boolean') {
                $ruleArray[] = 'boolean';
            }

            // Para fechas
            if ($schema->data_type === 'date') {
                $ruleArray[] = 'date';
            }

            $rules["atributos.{$schema->attribute_name}"] = implode('|', $ruleArray);
        }

        return $rules;
    }

    /**
     * Validar un valor contra el schema
     */
    public function validateValue($value): array
    {
        $errors = [];

        // Si no está activo, ignorar
        if (!$this->is_active) {
            return $errors;
        }

        // Requerido
        if ($this->is_required && empty($value)) {
            $errors[] = "El campo es requerido";
        }

        if (empty($value) && !$this->is_required) {
            return $errors; // Nullable y vacío = válido
        }

        // Validación por tipo
        switch ($this->data_type) {
            case 'text':
                if ($this->min_length && strlen($value) < $this->min_length) {
                    $errors[] = "Mínimo {$this->min_length} caracteres";
                }
                if ($this->max_length && strlen($value) > $this->max_length) {
                    $errors[] = "Máximo {$this->max_length} caracteres";
                }
                if ($this->regex_pattern && !preg_match("/{$this->regex_pattern}/", $value)) {
                    $errors[] = "Formato inválido";
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    $errors[] = "Debe ser un número";
                } else {
                    if ($this->min_value !== null && (float)$value < $this->min_value) {
                        $errors[] = "Mínimo {$this->min_value}";
                    }
                    if ($this->max_value !== null && (float)$value > $this->max_value) {
                        $errors[] = "Máximo {$this->max_value}";
                    }
                }
                break;

            case 'select':
                if ($this->options && !in_array($value, $this->options)) {
                    $errors[] = "Valor no permitido";
                }
                break;

            case 'date':
                if (!strtotime($value)) {
                    $errors[] = "Fecha inválida";
                }
                break;

            case 'boolean':
                if (!in_array($value, [true, false, 'true', 'false', 1, 0, '1', '0'])) {
                    $errors[] = "Debe ser verdadero o falso";
                }
                break;
        }

        return $errors;
    }
}
