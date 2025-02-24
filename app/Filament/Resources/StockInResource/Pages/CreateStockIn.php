<?php

namespace App\Filament\Resources\StockInResource\Pages;

use App\Filament\Resources\StockInResource;
use Exception;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStockIn extends CreateRecord
{
    protected static string $resource = StockInResource::class;

    // SAFETY MEASURES
    protected ?bool $hasDatabaseTransactions = true;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
