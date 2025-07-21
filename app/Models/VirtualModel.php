<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VirtualModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',              // ID de la tarea en KlingAI
        'model_name',           // Nombre del modelo (v1, etc.)
        'prompt',               // Prompt utilizado
        'gender',               // Género del modelo (opcional)
        'age_group',            // Grupo de edad (opcional)
        'skin_tone',            // Tono de piel (opcional)
        'aspect_ratio',         // Relación de aspecto solicitada
        'output_count',         // Cantidad de imágenes generadas
        'result_image_paths',   // Rutas relativas de imágenes generadas
        'status',               // Estado: processing, completed, failed
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
}
