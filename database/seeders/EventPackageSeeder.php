<?php

namespace Database\Seeders;

use App\Models\EventPackage;
use App\Models\Merchant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EventPackageSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::where('slug', 'douceur')->firstOrFail();

        $packages = [
            [
                'name' => 'Formule Anniversaire 10 personnes',
                'description' => 'Un assortiment parfait pour un anniversaire : mini-sucrés, salés et un gâteau.',
                'base_price' => 89.00,
                'min_quantity' => 10,
                'max_quantity' => 15,
                'includes' => ['mini_sucres' => 20, 'sales' => 20, 'gateau' => '1 gâteau 10 parts'],
            ],
            [
                'name' => 'Formule Mariage 50 personnes',
                'description' => 'Pièce montée et assortiment de mignardises pour votre grand jour.',
                'base_price' => 450.00,
                'min_quantity' => 50,
                'max_quantity' => 80,
                'includes' => ['piece_montee' => '1 pièce 50 parts', 'mignardises' => 100, 'mini_sales' => 100],
            ],
            [
                'name' => 'Formule Baptême 20 personnes',
                'description' => 'Douceurs et salés pour célébrer un baptême en famille.',
                'base_price' => 150.00,
                'min_quantity' => 20,
                'max_quantity' => 30,
                'includes' => ['mini_sucres' => 40, 'sales' => 40, 'gateau' => '1 gâteau 20 parts'],
            ],
        ];

        foreach ($packages as $p) {
            EventPackage::updateOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'merchant_id' => $merchant->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'description' => $p['description'],
                    'base_price' => $p['base_price'],
                    'min_quantity' => $p['min_quantity'],
                    'max_quantity' => $p['max_quantity'],
                    'includes' => $p['includes'],
                    'is_active' => true,
                ]
            );
        }
    }
}