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
        'weight',
        'price_component_1',
        'price_component_2',
        'price_component_3',
        'price_component_4',
        'price_component_5',
        'total_component_price',
        'selling_price',
    ];

    protected static function booted(): void
    {
        static::saving(function ($product) {
            $product->total_component_price = $product->price_component_1 +
                $product->price_component_2 +
                $product->price_component_3 +
                $product->price_component_4 +
                $product->price_component_5;
        });

        static::updated(function ($productMaster) {
            foreach ($productMaster->variants as $variant) {
                $variant->updateSkuAndName();
                $variant->updatePricesFromMaster();
            }
        });
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
