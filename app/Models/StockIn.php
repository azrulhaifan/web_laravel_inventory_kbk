<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockIn extends Model
{
    protected $fillable = [
        'warehouse_id',
        'supplier_id',
        'reference_type',
        'reference_id',
        'notes',
        'stock_in_status_id',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StockInStatus::class, 'stock_in_status_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockIn) {
            if (!isset($stockIn->reference_id)) {
                $today = now();
                $prefix = "SI-" . $today->format('ymd');

                // Get the latest number for today
                $latestStockIn = static::where('reference_id', 'like', $prefix . '%')
                    ->orderBy('reference_id', 'desc')
                    ->first();

                $nextNumber = $latestStockIn
                    ? intval(substr($latestStockIn->reference_id, -4)) + 1
                    : 1;

                $stockIn->reference_id = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
