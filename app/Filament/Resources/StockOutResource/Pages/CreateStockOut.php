<?php

namespace App\Filament\Resources\StockOutResource\Pages;

use App\Filament\Resources\StockOutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStockOut extends CreateRecord
{
    protected static string $resource = StockOutResource::class;

    // SAFETY MEASURES
    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
