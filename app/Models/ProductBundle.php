<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundle extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'description',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductBundleVariant::class);
    }
}
