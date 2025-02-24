<?php

namespace Database\Seeders;

use App\Models\StockInStatus;
use App\Models\StockOutStatus;
use Illuminate\Database\Seeder;

class StockOutStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Completed', 'color' => 'success'],
            ['name' => 'Draft / Pending', 'color' => 'gray'],
            ['name' => 'Cancelled', 'color' => 'danger'],
        ];

        foreach ($statuses as $status) {
            StockOutStatus::create($status);
        }
    }
}
