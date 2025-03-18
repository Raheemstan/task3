<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = ['type', 'threshold', 'rate', 'required_product'];

    protected $casts = [
        'threshold' => 'decimal:2',
        'rate' => 'decimal:2',
    ];
}
