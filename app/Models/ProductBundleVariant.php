<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundleVariant extends Model
{
    protected $fillable = [
        'product_master_id',
        'sku',
        'name',
        'description',
        'min_price',
    ];

    public function productMaster(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class, 'product_master_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleVariantItem::class);
    }

    // Tambahkan accessor untuk buying_price dan selling_price
    protected $appends = ['buying_price', 'selling_price'];

    public function getBuyingPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->productVariant->total_component_price ?? 0;
        });
    }

    public function getSellingPriceAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->productVariant->selling_price ?? 0;
        });
    }
}
