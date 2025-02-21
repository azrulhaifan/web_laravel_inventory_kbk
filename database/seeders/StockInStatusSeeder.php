<?php

namespace Database\Seeders;

use App\Models\StockInStatus;
use Illuminate\Database\Seeder;

class StockInStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Draft', 'color' => 'gray'],
            ['name' => 'Pending', 'color' => 'warning'],
            ['name' => 'Processing', 'color' => 'info'],
            ['name' => 'Completed', 'color' => 'success'],
            ['name' => 'Cancelled', 'color' => 'danger'],
        ];

        foreach ($statuses as $status) {
            StockInStatus::create($status);
        }
    }
}