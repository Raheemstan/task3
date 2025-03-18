<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{   
    protected $fillable = ['base_fee', 'cost_per_km', 'warehouse_lat', 'warehouse_lng', 'description'];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'cost_per_km' => 'decimal:2',
        'warehouse_lat' => 'decimal:6',
        'warehouse_lng' => 'decimal:6',
    ];
}
