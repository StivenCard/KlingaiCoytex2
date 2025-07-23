<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImageToVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'model_name',
        'prompt',
        'negative_prompt',
        'mode',
        'duration',
        'aspect_ratio',
        'input_image_paths',
        'result_video_paths',
        'status'
    ];

    protected $casts = [
        'input_image_paths' => 'array',
        'result_video_paths' => 'array'
    ];
}
