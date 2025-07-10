<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_type',
        'task_id',
        'model_name',
        'prompt',
        'hints_used',
        'status',
        'task_status',
        'task_status_msg',
        'request_data',
        'response_data',
        'error_details',
        'endpoint',
        'http_method',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'error_details' => 'array',
    ];
}
