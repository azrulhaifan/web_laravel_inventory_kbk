<?php

namespace App\Filament\Widgets;

use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', ProductVariant::count())
                ->description('Total active products')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Pending Stock In', StockIn::where('stock_in_status_id', 2)->count())
                ->description('Waiting to be processed')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('warning'),

            Stat::make('Pending Stock Out', StockOut::where('stock_out_status_id', 2)->count())
                ->description('Waiting to be processed')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('warning'),
        ];
    }
}
