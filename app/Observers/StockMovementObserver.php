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

        if ($stockMovement->type === 'opname' && $stockMovement->stock_movement_status_id === 1) {
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
                    $affectedBundleIds = ProductBundleVariantItem::where('product_variant_id', $stockMovement->product_variant_id)
                        ->pluck('product_bundle_variant_id');

                    foreach ($affectedBundleIds as $bundleId) {
                        $minStock = ProductBundleVariantItem::where('product_bundle_variant_id', $bundleId)
                            ->join('product_variants', 'product_bundle_variant_items.product_variant_id', '=', 'product_variants.id')
                            ->min('product_variants.current_stock') ?? 0;

                        ProductBundleVariant::where('id', $bundleId)->update(['min_stock' => $minStock]);
                    }

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
}
