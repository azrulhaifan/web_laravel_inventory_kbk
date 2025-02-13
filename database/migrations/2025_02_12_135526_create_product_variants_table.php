<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_master_id')->constrained()->cascadeOnDelete();
            $table->foreignId('color_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('size');
            $table->text('description')->nullable();
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('price_component_1', 10, 2)->default(0);
            $table->decimal('price_component_2', 10, 2)->default(0);
            $table->decimal('price_component_3', 10, 2)->default(0);
            $table->decimal('price_component_4', 10, 2)->default(0);
            $table->decimal('price_component_5', 10, 2)->default(0);
            $table->decimal('total_component_price', 10, 2)->default(0);
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
