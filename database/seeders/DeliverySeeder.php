<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Delivery::create([
            'base_fee' => 5.00,
            'cost_per_km' => 1.00,
            'warehouse_lat' => 9.9285,
            'warehouse_lng' => -8.8921,
            'description' => 'Jos warehouse',
        ]);
    }
}
