<?php

namespace App\Filament\Resources\StockOutResource\Pages;

use App\Filament\Resources\StockOutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockOuts extends ListRecords
{
    protected static string $resource = StockOutResource::class;

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
