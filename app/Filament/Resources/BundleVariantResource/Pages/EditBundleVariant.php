<?php

namespace App\Filament\Resources\BundleVariantResource\Pages;

use App\Filament\Resources\BundleVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBundleVariant extends EditRecord
{
    protected static string $resource = BundleVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
