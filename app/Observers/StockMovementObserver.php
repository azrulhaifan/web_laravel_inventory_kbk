<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Models\Stock;
use App\Models\ProductBundleVariantItem;
use App\Models\ProductBundleVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockMovementObserver
{
    private function updateStock(StockMovement $stockMovement)
    {
        return DB::transaction(function () use ($stockMovement) {
            // Lock the stock record
            $stock = Stock::where([
                'warehouse_id' => $stockMovement->warehouse_id,
                'product_variant_id' => $stockMovement->product_variant_id,
            ])->lockForUpdate()->first();

            if (!$stock) {
                $stock = Stock::create([
                    'warehouse_id' => $stockMovement->warehouse_id,
                    'product_variant_id' => $stockMovement->product_variant_id,
                    'quantity' => 0
                ]);
            }

            // Update quantity with locked record
            $stock->quantity += $stockMovement->quantity;
            $stock->save();

            // Lock product variant for update
            $productVariant = $stockMovement->productVariant()->lockForUpdate()->first();

            // Get total stock with lock
            $totalStock = Stock::where('product_variant_id', $stockMovement->product_variant_id)
                ->lockForUpdate()
                ->sum('quantity');

            $productVariant->update([
                'current_stock' => $totalStock
            ]);

            // Update bundle items with lock
            ProductBundleVariantItem::where('product_variant_id', $stockMovement->product_variant_id)
                ->lockForUpdate()
                ->update(['current_stock' => $totalStock]);

            // Update min_stock for affected bundles
            DB::statement("
                    UPDATE product_bundle_variants pbv
                    INNER JOIN (
                        SELECT pbi.product_bundle_variant_id, MIN(pv.current_stock) as min_stock
                        FROM product_bundle_variant_items pbi
                        JOIN product_variants pv ON pbi.product_variant_id = pv.id
                        WHERE pbi.product_bundle_variant_id IN (
                            SELECT DISTINCT product_bundle_variant_id 
                            FROM product_bundle_variant_items 
                            WHERE product_variant_id = ?
                        )
                        GROUP BY pbi.product_bundle_variant_id
                        FOR UPDATE
                    ) min_stocks ON pbv.id = min_stocks.product_bundle_variant_id
                    SET pbv.min_stock = min_stocks.min_stock
                ", [$stockMovement->product_variant_id]);

            return [
                'stock' => $stock,
                'total_stock' => $totalStock
            ];
        });
    }

    public function created(StockMovement $stockMovement): void
    {
        Log::info('StockMovement Observer -create- triggered', [
            'type' => $stockMovement->type,
            'status' => $stockMovement->stock_movement_status_id
        ]);

        if (($stockMovement->type === 'opname' || $stockMovement->type === 'in' || $stockMovement->type === 'out') &&
            (int) $stockMovement->stock_movement_status_id === 1
        ) {
            try {
                $result = $this->updateStock($stockMovement);

                Log::info('Stock updated successfully', [
                    'product_id' => $stockMovement->product_variant_id,
                    'warehouse_id' => $stockMovement->warehouse_id,
                    'new_stock' => $result['stock']->quantity,
                    'total_stock' => $result['total_stock']
                ]);
            } catch (\Exception $e) {
                Log::error('Error updating stock', [
                    'error' => $e->getMessage(),
                    'stock_movement_id' => $stockMovement->id
                ]);
            }
        }
    }

    public function updated(StockMovement $stockMovement): void
    {
        Log::info('StockMovement Observer -update- triggered', [
            'type' => $stockMovement->type,
            'status' => $stockMovement->stock_movement_status_id,
            'isDirty' => $stockMovement->isDirty('stock_movement_status_id'),
            'wasChanged' => $stockMovement->wasChanged('stock_movement_status_id'),
            'original' => $stockMovement->getOriginal('stock_movement_status_id'),
            'current' => $stockMovement->stock_movement_status_id,
        ]);

        if (
            ($stockMovement->type === 'in' || $stockMovement->type === 'out') &&
            (int) $stockMovement->stock_movement_status_id === 1 &&
            (int) $stockMovement->getOriginal('stock_movement_status_id') !== (int) $stockMovement->stock_movement_status_id
        ) {
            try {
                $result = $this->updateStock($stockMovement);

                Log::info('Stock updated successfully on status change', [
                    'product_id' => $stockMovement->product_variant_id,
                    'warehouse_id' => $stockMovement->warehouse_id,
                    'new_stock' => $result['stock']->quantity,
                    'total_stock' => $result['total_stock']
                ]);
            } catch (\Exception $e) {
                Log::error('Error updating stock on status change', [
                    'error' => $e->getMessage(),
                    'stock_movement_id' => $stockMovement->id
                ]);
            }
        }
    }
}
