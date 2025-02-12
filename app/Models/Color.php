<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
    ];

    protected static function booted(): void
    {
        static::updated(function ($color) {
            foreach ($color->productVariants as $variant) {
                $variant->updateSkuAndName();
            }
        });
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
