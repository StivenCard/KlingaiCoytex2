<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
