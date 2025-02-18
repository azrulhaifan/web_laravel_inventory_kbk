<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateStockIn extends CreateRecord
{
    protected static string $resource = StockInResource::class;

    public function getHeading(): Htmlable|string
    {
        return 'Add Stock';
    }

    public function getSubheading(): ?string
    {
        return 'Add stock from direct purchase / order';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return DB::transaction(function () use ($data, $items) {
            $movements = [];

            foreach ($items as $item) {
                $movements[] = StockMovement::create([
                    ...$data,
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $movements[0] ?? StockMovement::make(); // Return first movement or new instance
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
