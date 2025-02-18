<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('selling_price');
        });

        Schema::table('product_bundle_variant_items', function (Blueprint $table) {
            $table->integer('current_stock')->default(0)->after('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('current_stock');
        });

        Schema::table('product_bundle_variant_items', function (Blueprint $table) {
            $table->dropColumn('current_stock');
        });
    }
};
