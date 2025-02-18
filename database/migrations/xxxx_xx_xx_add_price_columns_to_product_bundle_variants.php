<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_bundle_variants', function (Blueprint $table) {
            $table->decimal('buying_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('product_bundle_variants', function (Blueprint $table) {
            $table->dropColumn(['buying_price', 'selling_price']);
        });
    }
};
