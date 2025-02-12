<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
    ];

    protected static function booted(): void
    {
        static::updated(function ($productMaster) {
            foreach ($productMaster->variants as $variant) {
                $variant->updateSkuAndName();
            }
        });
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
