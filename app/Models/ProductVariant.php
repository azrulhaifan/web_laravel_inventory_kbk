<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_master_id',
        'color_id',
        'sku',
        'name',
        'size',
        'description',
        'weight',
        'price_component_1',
        'price_component_2',
        'price_component_3',
        'price_component_4',
        'price_component_5',
        'total_component_price',
        'selling_price',
        'current_stock',
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
    }

    public function updateSkuAndName(): void
    {
        $this->sku = "{$this->productMaster->sku} - {$this->color->code} - {$this->size}";
        $this->name = "{$this->productMaster->name} - {$this->color->name} - {$this->size}";
        $this->save();
    }

    public function updatePricesFromMaster(): void
    {
        $master = $this->productMaster;

        $this->price_component_1 = $master->price_component_1;
        $this->price_component_2 = $master->price_component_2;
        $this->price_component_3 = $master->price_component_3;
        $this->price_component_4 = $master->price_component_4;
        $this->price_component_5 = $master->price_component_5;
        $this->total_component_price = $master->total_component_price;
        $this->selling_price = $master->selling_price;

        $this->save();
    }

    public function productMaster(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }
}
