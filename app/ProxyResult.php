<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProxyResult extends Model
{
    protected $fillable = [
        'proxy',
        'status',
        'type',
        'city',
        'speed',
        'proxy_check_id',
        'completed',
    ];

    protected $casts = [
        'status' => 'boolean',
        'completed' => 'boolean',
    ];
}
