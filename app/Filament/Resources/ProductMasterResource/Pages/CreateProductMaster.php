<?php

namespace App\Filament\Resources\ProductMasterResource\Pages;

use App\Filament\Resources\ProductMasterResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductMaster extends CreateRecord
{
    protected static string $resource = ProductMasterResource::class;
}
