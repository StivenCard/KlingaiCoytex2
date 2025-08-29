<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VirtualTryOn extends Model
{
    use HasFactory;

    /**
     * Campos que se pueden asignar de manera masiva.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',             // ID de la tarea en la API de Kling
        'user_id',             // ID del usuario asociado
        'model_name',          // Nombre del modelo usado (v1, v1.5, etc.)
        'model_type',          // 'default' o 'virtual' (tipo de modelo)
        'human_image_path',    // Ruta de la imagen humana usada
        'garments_type',       // 'single' o 'multiple'
        'cloth_image_path',    // Ruta de la imagen de prenda
        'output_count',        // Número de imágenes generadas
        'result_image_paths',  // Array de rutas de imágenes generadas
        'tokens',              // Tokens consumidos en la tarea
        'price',               // Costo calculado de la operación
        'status',              // 'processing', 'completed', 'failed'
    ];

    /**
     * Conversión automática de campos a tipos nativos.
     * Convierte `result_image_paths` en array cuando se consulta desde DB.
     *
     * @var array
     */
    protected $casts = [
        'result_image_paths' => 'array',
    ];

    /**
     * Obtiene las URLs públicas de todas las imágenes generadas.
     * Usa el helper Storage::url() para transformar las rutas locales
     * en rutas accesibles vía navegador (/storage/...).
     *
     * @return array Lista de URLs completas para previsualización
     */
    public function getAllPreviewUrlsAttribute(): array
    {
        return collect($this->result_image_paths ?? [])
            ->filter()
            ->map(fn($path) => Storage::url($path))
            ->all();
    }

    /**
     * Eventos de ciclo de vida del modelo.
     * Cuando se elimina un registro, también se eliminan sus archivos asociados
     * desde el disco configurado en `public`.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($img) {
            foreach ((array) $img->result_image_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
        });
    }
}
