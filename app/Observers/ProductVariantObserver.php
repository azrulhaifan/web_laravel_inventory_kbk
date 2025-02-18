<?php

namespace App\Observers;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// THIS OBSERVER TURNED OFF FOR NOW
// NEED MORE COMPLEX LOGIC TO WORK PROPERLY
// NEED CALCULATION WHEN MASTER PRODUCT IS UPDATED

class ProductVariantObserver
{
    public function updated(ProductVariant $variant): void
    {
        try {
            // Get all affected bundles with eager loaded relationships to prevent N+1
            $affectedBundleIds = $variant->bundleItems()
                ->pluck('product_bundle_variant_id');

            if ($affectedBundleIds->isEmpty()) {
                return;
            }

            // Update all affected bundles in single query
            DB::statement("
                UPDATE product_bundle_variants pbv
                INNER JOIN (
                    SELECT 
                        pbi.product_bundle_variant_id,
                        SUM(pv.total_component_price) as buying_price,
                        SUM(pv.selling_price) as selling_price
                    FROM product_bundle_variant_items pbi
                    JOIN product_variants pv ON pv.id = pbi.product_variant_id
                    WHERE pbi.product_bundle_variant_id IN (?)
                    GROUP BY pbi.product_bundle_variant_id
                ) prices ON prices.product_bundle_variant_id = pbv.id
                SET 
                    pbv.buying_price = prices.buying_price,
                    pbv.selling_price = prices.selling_price
            ", [$affectedBundleIds]);

            Log::info('Bundle prices updated successfully', [
                'variant_id' => $variant->id,
                'affected_bundles' => $affectedBundleIds
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating bundle prices', [
                'variant_id' => $variant->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function created(ProductVariant $variant): void
    {
        // Handle when new variant is created
    }

    public function deleted(ProductVariant $variant): void
    {
        // Handle when variant is deleted
    }
}
