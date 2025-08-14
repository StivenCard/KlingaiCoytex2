<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KlingUserLog extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'user_id',
        'image_to_video_id',
        'virtual_try_on_id',
        'virtual_model_id',
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con image_to_videos
    public function imageToVideo()
    {
        return $this->belongsTo(ImageToVideo::class);
    }

    // Relación con virtual_try_ons
    public function virtualTryOn()
    {
        return $this->belongsTo(VirtualTryOn::class);
    }

    // Relación con virtual_models
    public function virtualModel()
    {
        return $this->belongsTo(VirtualModel::class);
    }
}
