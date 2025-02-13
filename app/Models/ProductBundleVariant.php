<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBundleVariant extends Model
{
    protected $fillable = [
        'product_bundle_id',
        'sku',
        'name',
        'description',
    ];

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductBundleVariantItem::class);
    }
}