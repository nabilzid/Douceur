<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::updateOrCreate(
            ['code' => 'BIENVENUE10'],
            [
                'type' => 'percent',
                'value' => 10.00,
                'min_order_amount' => 30.00,
                'max_uses' => 100,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'LIVRAISONOFFERTE'],
            [
                'type' => 'fixed',
                'value' => 5.00,
                'min_order_amount' => 20.00,
                'max_uses' => 50,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'is_active' => true,
            ]
        );
    }
}