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
        'user_id',             // ID del usuario asociado
        'model_name',          // Nombre del modelo usado
        'model_type',          // 'default', 'virtual' o 'upload'
        'human_image_path',    // Ruta de la imagen humana usada
        'garments_type',       // 'single' o 'multiple'
        'cloth_image_path',    // Ruta de la imagen de prenda
        'output_count',        // Número de imágenes generadas
        'result_image_paths',  // Array de rutas de imágenes generadas
        'tokens',              // Tokens consumidos en la tarea
        'price',               // Costo calculado de la operación
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

    /* public function getAllPreviewUrlsAttribute(): array
    {
        return collect($this->result_image_paths ?? [])
            ->filter()
            ->map(fn($path) => Storage::disk('s3')->temporaryUrl(
                $path,
                now()->addMinutes(30)
            ))
            ->all();
    } */

    /**
     * Al eliminar el registro se borran sus resultados y las copias de entrada que creó este módulo.
     * Las rutas de otros módulos (modelos predeterminados o virtuales) nunca se tocan.
     */
    protected static function booted()
    {
        static::deleting(function ($img) {
            foreach ((array) $img->result_image_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
            /* foreach ((array) $img->result_image_paths as $path) {
                Storage::disk('s3')->delete(
                    preg_replace('/^storage\//', '', $path)
                );
            } */

            foreach ([$img->human_image_path, $img->cloth_image_path] as $input) {
                if ($input && str_starts_with($input, 'klingai/tryon_inputs/')) {
                    Storage::disk('public')->delete($input);
                }
            }
            /* foreach ([$img->human_image_path, $img->cloth_image_path] as $input) {
                if ($input && str_starts_with($input, 'klingai/tryon_inputs/')) {
                    Storage::disk('s3')->delete($input);
                }
            } */
        });
    }
}
