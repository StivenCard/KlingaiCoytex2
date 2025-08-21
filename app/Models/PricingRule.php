<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $fillable = [
        'model_name', 
        'mode', 
        'duration', 
        'tokens', 
        'price'
    ];
}
