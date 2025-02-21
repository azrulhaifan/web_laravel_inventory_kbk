<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockInStatus extends Model
{
    protected $fillable = [
        'name',
        'color',
    ];

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }
}