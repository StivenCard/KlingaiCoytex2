<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VirtualTryOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'model_name',
        'model_type',
        'human_image_path',
        'garments_type',
        'cloth_image_path',
        'output_count',
        'result_image_paths',
        'status',
    ];

    protected $casts = [
        'result_image_paths' => 'array',
    ];

    // 🔥 ACCESSORS PARA FRONTEND
    public function getPreviewUrlAttribute(): ?string
    {
        if (empty($this->result_image_paths)) {
            return null;
        }

        return Storage::url($this->result_image_paths[0]);
    }

    public function getDisplayNameAttribute(): string
    {
        $modelType = ucfirst($this->model_type);
        return "Try-On #{$this->id} ({$modelType})";
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i');
    }

    public function getPreviewPathAttribute(): ?string
    {
        return $this->result_image_paths[0] ?? null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'processing' => 'badge-warning',
            'completed' => 'badge-success',
            'failed' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'processing' => 'En proceso',
            'completed' => 'Completado',
            'failed' => 'Fallido',
            default => 'Pendiente'
        };
    }

    public function getGarmentTypeTextAttribute(): string
    {
        return match($this->garments_type) {
            'single' => 'Prenda única',
            'multiple' => 'Múltiples prendas',
            default => 'N/A'
        };
    }
}
