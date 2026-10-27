<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        DeliveryZone::updateOrCreate(
            ['name' => 'Paris intra-muros'],
            [
                'postal_codes' => ['75001','75002','75003','75004','75005','75006','75007','75008','75009','75010','75011','75012','75013','75014','75015','75016','75017','75018','75019','75020'],
                'delivery_fee' => 5.00,
                'free_delivery_threshold' => 50.00,
                'min_order_amount' => 20,
                'estimated_delivery_minutes' => 60,
                'is_active' => true,
            ]
        );

        DeliveryZone::updateOrCreate(
            ['name' => 'Petite couronne'],
            [
                'postal_codes' => ['92*','93*','94*'],
                'delivery_fee' => 8.00,
                'free_delivery_threshold' => 70.00,
                'min_order_amount' => 30,
                'estimated_delivery_minutes' => 90,
                'is_active' => true,
            ]
        );
    }
}