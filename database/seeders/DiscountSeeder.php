<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 5% discount for orders over $200.
        Discount::create([
            'type' => 'order_total',
            'threshold' => 200,
            'rate' => 0.05,
        ]);

        // 10% discount for a specific product combo.
        Discount::create([
            'type' => 'product_combo',
            'threshold' => 0,
            'rate' => 0.10,
            'required_product' => json_encode(['Laptop', 'Headphones']),
        ]);
    }
}
