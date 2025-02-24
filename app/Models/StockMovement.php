<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_variant_id',
        'product_bundle_variant_id',
        'stock_movement_status_id',
        'stock_in_id',
        'reseller_id',
        'supplier_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productBundleVariant(): BelongsTo
    {
        return $this->belongsTo(ProductBundleVariant::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StockMovementStatus::class, 'stock_movement_status_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockIn(): BelongsTo
    {
        return $this->belongsTo(StockIn::class);
    }
}
