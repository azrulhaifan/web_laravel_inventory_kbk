<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockMovementObserver
{
    public function created(StockMovement $stockMovement): void
    {
        Log::info('StockMovement Observer triggered', [
            'type' => $stockMovement->type,
            'status' => $stockMovement->stock_movement_status_id
        ]);

        if ($stockMovement->type === 'opname' && $stockMovement->stock_movement_status_id === 1) {
            try {
                // Get or create stock record
                $stock = Stock::updateOrCreate(
                    [
                        'warehouse_id' => $stockMovement->warehouse_id,
                        'product_variant_id' => $stockMovement->product_variant_id,
                    ],
                    [
                        'quantity' => DB::raw('quantity + ' . $stockMovement->quantity)
                    ]
                );

                // Update product variant's total stock
                $totalStock = Stock::where('product_variant_id', $stockMovement->product_variant_id)
                    ->sum('quantity');

                $stockMovement->productVariant->update([
                    'current_stock' => $totalStock
                ]);

                Log::info('Stock updated successfully', [
                    'product_id' => $stockMovement->product_variant_id,
                    'warehouse_id' => $stockMovement->warehouse_id,
                    'new_stock' => $stock->quantity,
                    'total_stock' => $totalStock
                ]);
            } catch (\Exception $e) {
                Log::error('Error updating stock', [
                    'error' => $e->getMessage(),
                    'stock_movement_id' => $stockMovement->id
                ]);
            }
        }
    }
}
