<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class ImageToVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
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

    public function logs (){
        return $this->hasMany(KlingUserLog::class);
    }

    protected static function booted()
    {
        static::deleting(function ($video) {
            foreach ((array) $video->result_video_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
            foreach ((array) $video->input_image_paths as $path) {
                Storage::disk('public')->delete(preg_replace('/^storage\//', '', $path));
            }
        });
    }
}
