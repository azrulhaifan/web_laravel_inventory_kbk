<?php

namespace App\Filament\Resources\ProductMasterResource\Pages;

use App\Filament\Resources\ProductMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductMaster extends EditRecord
{
    protected static string $resource = ProductMasterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
