<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('product_bundle_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_bundle_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('product_bundle_variant_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_bundle_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundle_variant_items');
        Schema::dropIfExists('product_bundle_variants');
        Schema::dropIfExists('product_bundles');
    }
};
