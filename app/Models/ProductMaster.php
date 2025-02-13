<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
            // Bulk update all variants prices
            $productMaster->variants()->update([
                'price_component_1' => $productMaster->price_component_1,
                'price_component_2' => $productMaster->price_component_2,
                'price_component_3' => $productMaster->price_component_3,
                'price_component_4' => $productMaster->price_component_4,
                'price_component_5' => $productMaster->price_component_5,
                'total_component_price' => $productMaster->total_component_price,
                'selling_price' => $productMaster->selling_price,
            ]);

            // Bulk update SKU and name using raw SQL for better performance
            $productMaster->variants()
                ->join('colors', 'product_variants.color_id', '=', 'colors.id')
                ->update([
                    'product_variants.sku' => DB::raw("CONCAT('{$productMaster->sku}', ' - ', colors.code, ' - ', product_variants.size)"),
                    'product_variants.name' => DB::raw("CONCAT('{$productMaster->name}', ' - ', colors.name, ' - ', product_variants.size)"),
                ]);
        });
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
