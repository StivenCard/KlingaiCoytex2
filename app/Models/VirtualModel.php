<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class VirtualModel
 *
 * Representa un modelo virtual generado mediante la API de KlingAI.
 * Incluye atributos relacionados con la tarea de generación, metadatos
 * del modelo y rutas de las imágenes generadas.
 */
class VirtualModel extends Model
{
    use HasFactory;

    /**
     * Atributos que se pueden asignar de forma masiva.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',              // ID de la tarea en KlingAI
        'user_id',              // ID del usuario propietario
        'model_name',           // Nombre del modelo (ej: v1, etc.)
        'prompt',               // Prompt utilizado para la generación
        'gender',               // Género del modelo (opcional)
        'age_group',            // Grupo de edad (opcional)
        'skin_tone',            // Tono de piel (opcional)
        'aspect_ratio',         // Relación de aspecto solicitada
        'output_count',         // Cantidad de imágenes generadas
        'tokens',               // Tokens consumidos en la generación
        'price',                // Precio calculado de la operación
        'result_image_paths',   // Rutas relativas de imágenes generadas
        'status',               // Estado: processing, completed, failed
    ];

    /**
     * Conversión automática de atributos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'result_image_paths' => 'array',
    ];

    /**
     * Obtiene todas las URLs públicas de previsualización de las imágenes generadas.
     *
     * @return array<int, string> Lista de URLs accesibles públicamente.
     */
    public function getAllPreviewUrlsAttribute(): array
    {
        return collect($this->result_image_paths ?? [])
            ->filter()
            ->map(fn($path) => Storage::url($path))
            ->all();
    }

    /**
     * Evento "booted" del modelo.
     * Elimina automáticamente las imágenes asociadas en el disco
     * cuando se elimina el registro de la base de datos.
     *
     * @return void
     */
    protected static function booted()
    {
        //En el callback se hace automaticamente un model::find
        static::deleting(function ($model) {
            foreach ((array) $model->result_image_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
        });
    }
}
