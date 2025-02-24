<?php

namespace Database\Seeders;

use App\Models\StockInStatus;
use Illuminate\Database\Seeder;

class StockInStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Draft / Pending', 'color' => 'gray'],
            ['name' => 'Completed', 'color' => 'success'],
            ['name' => 'Cancelled', 'color' => 'danger'],
        ];

        foreach ($statuses as $status) {
            StockInStatus::create($status);
        }
    }
}
