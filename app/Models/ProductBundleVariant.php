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
}
