<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OmniModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_id',
        'model_name',
        'model_type',
        'prompt',
        'human_image_path',
        'garments_type',
        'cloth_image_path',
        'output_count',
        'aspect_ratio',
        'resolution',
        'tokens',
        'price',
        'result_image_paths',
        'status'
    ];

    protected $casts = [
        'result_image_paths' => 'array',
    ];

    public function getAllPreviewUrlsAttribute(): array
    {
        return collect($this->result_image_paths ?? [])
            ->filter() //Elimina cualquier valor nulo o vacío
            ->map(fn($path) => Storage::url($path))
            ->all();
    }


    protected static function booted()
    {
        static::deleting(function ($omniModel) {
            // Usamos disk('public') explícitamente para asegurar dónde debe borrar
            if ($omniModel->human_image_path) {
                Storage::disk('public')->delete($omniModel->human_image_path);
            }

            if ($omniModel->cloth_image_path) {
                Storage::disk('public')->delete($omniModel->cloth_image_path);
            }

            if (is_array($omniModel->result_image_paths)) {
                foreach ($omniModel->result_image_paths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
        });
    }

    /*version para s3
    protected static function booted()
    {
        static::deleting(function ($omniModel) {
            // Eliminar la imagen humana asociada
            if ($omniModel->human_image_path) {
                Storage::delete($omniModel->human_image_path);
            }

            // Eliminar la imagen de prenda asociada
            if ($omniModel->cloth_image_path) {
                Storage::delete($omniModel->cloth_image_path);
            }

            // Eliminar todas las imágenes generadas asociadas
            if (is_array($omniModel->result_image_paths)) {
                foreach ($omniModel->result_image_paths as $path) {
                    Storage::delete($path);
                }
            }
        });
    }
    */
}
