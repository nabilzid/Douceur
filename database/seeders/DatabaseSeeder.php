<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            ProductTypeSeeder::class,
            MerchantSeeder::class,
            ProductSeeder::class,
            EventPackageSeeder::class,
            DeliveryZoneSeeder::class,
            DeliverySlotSeeder::class,
            CouponSeeder::class,
        ]);
    }
}