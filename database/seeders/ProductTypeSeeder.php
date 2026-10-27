<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Gâteaux',       'slug' => 'cake',          'icon' => '🎂', 'sort_order' => 1],
            ['name' => 'Secs',          'slug' => 'dry_pastry',    'icon' => '🍪', 'sort_order' => 2],
            ['name' => 'Salés',         'slug' => 'savory',        'icon' => '🥐', 'sort_order' => 3],
            ['name' => 'Sucrés',        'slug' => 'sweet_bite',    'icon' => '🧁', 'sort_order' => 4],
            ['name' => 'Formules du jour', 'slug' => 'event_package', 'icon' => '🎁', 'sort_order' => 5],
        ];

        foreach ($types as $type) {
            ProductType::updateOrCreate(['slug' => $type['slug']], $type);
        }
    }
}