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
    public function created(StockMovement $stockMovement): void
    {
        Log::info('StockMovement Observer triggered', [
            'type' => $stockMovement->type,
            'status' => $stockMovement->stock_movement_status_id
        ]);

        if (($stockMovement->type === 'opname' || $stockMovement->type === 'in' || $stockMovement->type === 'out') &&
            (int) $stockMovement->stock_movement_status_id === 1
        ) {
            try {
                DB::transaction(function () use ($stockMovement) {
                    // Get or create stock record
                    $stock = Stock::updateOrCreate(
                        [
                            'warehouse_id' => $stockMovement->warehouse_id,
                            'product_variant_id' => $stockMovement->product_variant_id,
                        ],
                        [
                            'quantity' => DB::raw('quantity + ' . $stockMovement->quantity) // quantity will be negative for 'out' type
                        ]
                    );

                    // Update product variant's total stock
                    $totalStock = Stock::where('product_variant_id', $stockMovement->product_variant_id)
                        ->sum('quantity');

                    $stockMovement->productVariant->update([
                        'current_stock' => $totalStock
                    ]);

                    // Update bundle items current_stock
                    ProductBundleVariantItem::where('product_variant_id', $stockMovement->product_variant_id)
                        ->update(['current_stock' => $totalStock]);

                    // Update min_stock for affected bundles in single query
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
                        ) min_stocks ON pbv.id = min_stocks.product_bundle_variant_id
                        SET pbv.min_stock = min_stocks.min_stock
                    ", [$stockMovement->product_variant_id]);

                    Log::info('Stock updated successfully', [
                        'product_id' => $stockMovement->product_variant_id,
                        'warehouse_id' => $stockMovement->warehouse_id,
                        'new_stock' => $stock->quantity,
                        'total_stock' => $totalStock
                    ]);
                });
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
            Log::info('StockMovement Observer -update- started', [
                'type' => $stockMovement->type,
                'status' => $stockMovement->stock_movement_status_id
            ]);

            try {
                DB::transaction(function () use ($stockMovement) {
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

                    // Update bundle items current_stock
                    ProductBundleVariantItem::where('product_variant_id', $stockMovement->product_variant_id)
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
                        ) min_stocks ON pbv.id = min_stocks.product_bundle_variant_id
                        SET pbv.min_stock = min_stocks.min_stock
                    ", [$stockMovement->product_variant_id]);

                    Log::info('Stock updated successfully on status change', [
                        'product_id' => $stockMovement->product_variant_id,
                        'warehouse_id' => $stockMovement->warehouse_id,
                        'new_stock' => $stock->quantity,
                        'total_stock' => $totalStock
                    ]);
                });
            } catch (\Exception $e) {
                Log::error('Error updating stock on status change', [
                    'error' => $e->getMessage(),
                    'stock_movement_id' => $stockMovement->id
                ]);
            }
        }
    }
}
