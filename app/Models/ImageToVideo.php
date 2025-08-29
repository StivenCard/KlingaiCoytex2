<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImageToVideo
 *
 * Representa la conversión de una o varias imágenes a un video
 * mediante la API de KlingAI. Contiene metadatos del proceso,
 * rutas de entrada (imágenes) y salida (videos), así como
 * información de consumo de tokens y costo.
 */
class ImageToVideo extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',             // ID de la tarea en KlingAI
        'user_id',             // Usuario asociado
        'model_name',          // Nombre del modelo utilizado
        'prompt',              // Prompt principal
        'negative_prompt',     // Prompt negativo (opcional)
        'mode',                // Modo de generación (ej: animación, transición, etc.)
        'duration',            // Duración del video en segundos
        'aspect_ratio',        // Relación de aspecto del video
        'tokens',              // Tokens consumidos
        'price',               // Costo calculado
        'input_image_paths',   // Rutas relativas de imágenes de entrada
        'result_video_paths',  // Rutas relativas de videos generados
        'status'               // Estado: processing, completed, failed
    ];

    /**
     * Conversión automática de atributos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'input_image_paths'  => 'array',
        'result_video_paths' => 'array',
    ];

    /**
     * Evento "booted" del modelo.
     * Elimina automáticamente los videos generados y las imágenes de entrada
     * asociadas en el disco cuando se elimina el registro de la base de datos.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($video) {
            foreach ((array) $video->result_video_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
            foreach ((array) $video->input_image_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
        });
    }
}
