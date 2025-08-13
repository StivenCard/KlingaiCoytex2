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
        'user_id',
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

    public function getAllPreviewUrlsAttribute(): array
    {
        return collect($this->result_image_paths ?? [])
            ->filter()
            ->map(fn($path) => Storage::url($path))
            ->all();
    }

    public function logs(){
        return $this->hasMany(KlingUserLog::class);
    }

    protected static function booted()
    {
        static::deleting(function ($img) {
            foreach ((array) $img->result_image_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
        });
    }
}

