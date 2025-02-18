<?php

namespace Database\Seeders;

use App\Models\StockMovementStatus;
use Illuminate\Database\Seeder;

class StockMovementStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Complete Order',
                'code' => 'SCO',
                'description' => 'Status yang menandakan order selesai, quantity harus dihitung sebagai stock',
            ],
            [
                'name' => 'Draft Orders',
                'code' => 'SDO',
                'description' => 'Status yang menandakan order di inisialisasi, quantity belum dihitung sebagai stock',
            ],
            [
                'name' => 'Draft Canceled',
                'code' => 'SDC',
                'description' => 'Status yang menandakan order dibatalkan, quantity tidak dihitung sebagai stock',
            ],
        ];

        foreach ($statuses as $status) {
            StockMovementStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}