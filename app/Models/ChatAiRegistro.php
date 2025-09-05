<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAiRegistro extends Model
{
    protected $fillable = [
        'user_id',
        'user_message',
        'ai_response',
        'tokens_prompt',
        'tokens_thought',
        'tokens_response',
        'tokens_total',
        'price'
    ];

    /**
     * Calcula el precio de una solicitud de ChatAI basado en los tokens de entrada y salida.
     *
     * @param int $promptTokens La cantidad de tokens de la entrada del usuario.
     * @param int $responseTokens La cantidad de tokens de la respuesta de la IA.
     * @return float El costo total de la solicitud redondeado a 8 decimales.
     */
    public static function calculatePrice($promptTokens, $responseTokens)
    {
        $precioEntrada = ($promptTokens * 0.30) / 1000000;
        $precioSalida = ($responseTokens * 2.50) / 1000000;
        $precioTotal = $precioEntrada + $precioSalida;
        
        return round($precioTotal, 8);
    }
}
