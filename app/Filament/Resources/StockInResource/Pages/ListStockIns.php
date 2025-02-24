<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockIns extends ListRecords
{
    protected static string $resource = StockInResource::class;

    public function getSubheading(): ?string
    {
        return 'Only drafted / pending record can be updated.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
