<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_type',       // 'virtual_model' o 'virtual_try_on'
        'task_id',              // ID de la tarea asignado por Kling
        'user_id',              // ID del usuario que realizó la acción
        'model_name',           // Nombre del modelo usado (ej: 'kling-v1-5')
        'prompt',               // Prompt utilizado (si aplica)
        'status',               // Estado general del registro ('completed', 'failed')
        'task_status',          // Estado específico de la tarea en Kling ('succeed', 'pending', etc.)
        'request_data',         // Array con payload enviado (solo en virtual_model)
        'human_image_path',     // Ruta de imagen humana (solo en virtual_try_on)
        'cloth_image_path',     // Ruta de imagen de prenda (solo en virtual_try_on)
        'response_data',        // Respuesta completa de la API (array)
        'error_details',        // Detalles del error en caso de fallo
        'endpoint',             // URL completa del endpoint invocado
        'http_method',          // Método HTTP usado ('GET' o 'POST')
    ];

    protected $casts = [
        'request_data'   => 'array',
        'response_data'  => 'array',
        'error_details'  => 'array',
        'input_image_paths' => 'array', // Solo para multi_image_to_video
    ];
}
