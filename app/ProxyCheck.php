<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProxyCheck extends Model
{
    protected $fillable = [
        'completed',
        'time'
    ];

    public function proxyResults(): HasMany
    {
        return $this->hasMany(ProxyResult::class);
    }
}
