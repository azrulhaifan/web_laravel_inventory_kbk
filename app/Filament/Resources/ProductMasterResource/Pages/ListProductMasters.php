<?php

namespace App\Filament\Resources\ProductMasterResource\Pages;

use App\Filament\Resources\ProductMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductMasters extends ListRecords
{
    protected static string $resource = ProductMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
