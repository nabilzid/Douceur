<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MerchantSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'admin@douceur.test'],
            [
                'name' => 'Admin Douceur',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+33 6 00 00 00 01',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Pâtissier (gère la boutique)
        $merchantUser = User::updateOrCreate(
            ['email' => 'patissier@douceur.test'],
            [
                'name' => 'Pâtissier Douceur',
                'password' => Hash::make('password'),
                'role' => 'merchant',
                'phone' => '+33 6 00 00 00 02',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Boutique unique
        Merchant::updateOrCreate(
            ['slug' => 'douceur'],
            [
                'user_id' => $merchantUser->id,
                'shop_name' => 'Douceur',
                'siret' => '12345678901234',
                'description' => 'Pâtisseries artisanales faites maison.',
                'address' => '12 rue de la Pâtisserie',
                'postal_code' => '75011',
                'city' => 'Paris',
                'phone' => '+33 1 23 45 67 89',
                'email' => 'patissier@douceur.test',
                'status' => 'approved',
                'is_active' => true,
                'commission_rate' => 10.00,
            ]
        );

        // Client
        User::updateOrCreate(
            ['email' => 'client@douceur.test'],
            [
                'name' => 'Jean Client',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+33 6 00 00 00 03',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}