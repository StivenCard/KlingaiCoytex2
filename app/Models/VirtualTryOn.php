<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VirtualTryOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',             // ID de la tarea en la API de Kling
        'model_name',          // Nombre del modelo usado (v1, v1.5, etc.)
        'model_type',          // 'default' o 'virtual' (tipo de modelo)
        'human_image_path',    // Ruta de la imagen humana usada
        'garments_type',       // 'single' o 'multiple'
        'cloth_image_path',    // Ruta de la imagen de prenda
        'output_count',        // Número de imágenes generadas
        'result_image_paths',  // Array de rutas de imágenes generadas
        'status',              // 'processing', 'completed', 'failed'
    ];

    protected $casts = [
        'result_image_paths' => 'array',
    ];

    /**
     * Devuelve un nombre amigable para mostrar en vistas.
     * Ejemplo: Try-On #4 (Virtual)
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $modelType = ucfirst($this->model_type);
        return "Try-On #{$this->id} ({$modelType})";
    }

    /**
     * Devuelve la fecha formateada de creación del try-on.
     *
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i');
    }

    /**
     * Devuelve todas las URLs públicas de las imágenes generadas.
     *
     * @return string[]
     */
    public function getAllPreviewUrlsAttribute(): array
    {
        return collect($this->result_image_paths)
            ->map(fn($path) => Storage::url($path))
            ->toArray();
    }

    /**
     * Devuelve la clase de Bootstrap correspondiente al estado.
     * Ejemplo: badge-success para 'completed'
     *
     * @return string
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'processing' => 'badge-warning',
            'completed'  => 'badge-success',
            'failed'     => 'badge-danger',
            default      => 'badge-secondary',
        };
    }

    /**
     * Devuelve el texto amigable en español para el estado.
     *
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'processing' => 'En proceso',
            'completed'  => 'Completado',
            'failed'     => 'Fallido',
            default      => 'Pendiente',
        };
    }

    /**
     * Devuelve una descripción del tipo de prenda cargada.
     * Ejemplo: 'Prenda única' o 'Múltiples prendas'
     *
     * @return string
     */
    public function getGarmentTypeTextAttribute(): string
    {
        return match($this->garments_type) {
            'single'   => 'Prenda única',
            'multiple' => 'Múltiples prendas',
            default    => 'N/A',
        };
    }
}
