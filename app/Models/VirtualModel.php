<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VirtualModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'model_name',
        'prompt',
        'gender',
        'age_group',
        'skin_tone',
        'hints',
        'aspect_ratio',
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
        return "Virtual Model #{$this->id}";
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('Y-m-d H:i');
    }

    public function getPreviewPathAttribute(): ?string
    {
        return $this->result_image_paths[0] ?? null;
    }
}
