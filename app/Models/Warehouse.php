<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'address',
        'description',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
