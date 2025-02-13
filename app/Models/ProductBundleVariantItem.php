<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundleVariantItem extends Model
{
    protected $fillable = [
        'product_bundle_variant_id',
        'product_variant_id',
    ];

    public function bundleVariant(): BelongsTo
    {
        return $this->belongsTo(ProductBundleVariant::class, 'product_bundle_variant_id');
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
