<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'aspect_ratio',
        'output_count',
        'result_image_paths',
        'status',
    ];

    protected $casts = [
        'result_image_paths' => 'array',
    ];
}

