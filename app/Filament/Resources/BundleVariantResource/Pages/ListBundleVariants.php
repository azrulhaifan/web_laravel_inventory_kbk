<?php

namespace App\Filament\Resources\BundleVariantResource\Pages;

use App\Filament\Resources\BundleVariantResource;
use Filament\Resources\Pages\ListRecords;

class ListBundleVariants extends ListRecords
{
    protected static string $resource = BundleVariantResource::class;

    public function getSubheading(): ?string
    {
        return 'View all bundle variants and their available stock, no create, update or delete functionality';
    }
}
