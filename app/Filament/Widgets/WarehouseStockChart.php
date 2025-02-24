<?php

namespace App\Filament\Widgets;

use App\Models\Warehouse;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class WarehouseStockChart extends ChartWidget
{
    protected static ?string $heading = 'Warehouse Stock Overview';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Warehouse::select('warehouses.name')
            ->selectRaw('COUNT(DISTINCT stocks.product_variant_id) as total_variants')
            ->selectRaw('SUM(stocks.quantity) as total_stock')
            ->leftJoin('stocks', 'warehouses.id', '=', 'stocks.warehouse_id')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Stock',
                    'data' => $data->pluck('total_stock')->map(fn($value) => $value ?? 0)->toArray(),
                    'backgroundColor' => '#fbbf24',
                    'borderColor' => '#f59e0b',
                ],
                [
                    'label' => 'Total Variants',
                    'data' => $data->pluck('total_variants')->map(fn($value) => $value ?? 0)->toArray(),
                    'backgroundColor' => '#60a5fa',
                    'borderColor' => '#3b82f6',
                ]
            ],
            'labels' => $data->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
