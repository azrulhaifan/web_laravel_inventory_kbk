<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_master_id',
        'color_id',
        'sku',
        'name',
        'size',
        'description',
    ];

    public function updateSkuAndName(): void
    {
        $this->sku = "{$this->productMaster->sku} - {$this->color->code} - {$this->size}";
        $this->name = "{$this->productMaster->name} - {$this->color->name} - {$this->size}";
        $this->save();
    }

    public function productMaster(): BelongsTo
    {
        return $this->belongsTo(ProductMaster::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }
}
