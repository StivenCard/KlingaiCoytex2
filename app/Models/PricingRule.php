<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PricingRule
 *
 * Representa una regla de precios para un modelo específico de KlingAI.
 * Permite definir costos en función del modelo, modo de uso, duración
 * y consumo de tokens.
 */
class PricingRule extends Model
{
    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'model_name', // Nombre del modelo (ej: 'kolors-virtual-try-on-v1-5')
        'mode',       // Modo de uso (ej: 'image-to-video', 'virtual-try-on')
        'duration',   // Duración en segundos (aplicable a video)
        'tokens',     // Cantidad de tokens asociados a la operación
        'price',      // Precio calculado en base a tokens o reglas de negocio
    ];
}
