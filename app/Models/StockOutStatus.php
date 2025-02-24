<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOutStatus extends Model
{
    protected $fillable = [
        'name',
        'color',
    ];

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }
}
