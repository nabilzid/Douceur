<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $merchant = Merchant::where('slug', 'douceur')->firstOrFail();

        $cake = ProductType::where('slug', 'cake')->firstOrFail();
        $dry = ProductType::where('slug', 'dry_pastry')->firstOrFail();
        $savory = ProductType::where('slug', 'savory')->firstOrFail();
        $sweet = ProductType::where('slug', 'sweet_bite')->firstOrFail();
        $eventPackage = ProductType::where('slug', 'event_package')->firstOrFail();

        $products = [
            ['type' => $cake->id, 'name' => 'Gâteau au chocolat', 'price' => 28.00, 'stock' => 10, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Gâteau moelleux au chocolat noir 70%.'],
            ['type' => $cake->id, 'name' => 'Fraisier', 'price' => 32.00, 'stock' => 5, 'allergens' => ['gluten', 'lait', 'œufs', 'fruits à coque'], 'desc' => 'Fraisier traditionnel avec crème mousseline.'],
            ['type' => $cake->id, 'name' => 'Tarte citron meringuée', 'price' => 24.00, 'stock' => 8, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Tarte citron avec meringue italienne.'],
            ['type' => $dry->id, 'name' => 'Cookies chocolat', 'price' => 2.50, 'stock' => 50, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Cookies moelleux aux pépites de chocolat.'],
            ['type' => $dry->id, 'name' => 'Madeleines', 'price' => 1.80, 'stock' => 40, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Madeleines traditionnelles.'],
            ['type' => $dry->id, 'name' => 'Financiers', 'price' => 2.00, 'stock' => 30, 'allergens' => ['gluten', 'lait', 'œufs', 'fruits à coque'], 'desc' => 'Financiers à la poudre d\'amande.'],
            ['type' => $savory->id, 'name' => 'Mini quiches', 'price' => 1.50, 'stock' => 60, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Mini quiches lorraines.'],
            ['type' => $savory->id, 'name' => 'Feuilletés saucisse', 'price' => 1.20, 'stock' => 80, 'allergens' => ['gluten', 'lait', 'moutarde'], 'desc' => 'Feuilletés saucisse cocktail.'],
            ['type' => $sweet->id, 'name' => 'Mini éclairs', 'price' => 2.20, 'stock' => 25, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Mini éclairs au chocolat.'],
            ['type' => $sweet->id, 'name' => 'Mini tartelettes fraises', 'price' => 2.00, 'stock' => 30, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Mini tartelettes aux fraises fraîches.'],
            ['type' => $eventPackage->id, 'name' => '3 mille-feuilles achetés = 1 offert', 'price' => 9.00, 'stock' => 50, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Pour 3 mille-feuilles achetés, le 4ème est offert.'],
            ['type' => $eventPackage->id, 'name' => 'Gâteau 6 parts — Idéal anniversaire', 'price' => 22.00, 'stock' => 20, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Un gâteau de 6 parts, parfait pour un petit anniversaire.'],
            ['type' => $eventPackage->id, 'name' => 'Coffret découverte — 10 mini-sucrés', 'price' => 12.00, 'stock' => 30, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Un assortiment de 10 mini-sucrés variés.'],
            ['type' => $eventPackage->id, 'name' => 'Offre du week-end — 2 tartes pour le prix d\'1', 'price' => 18.00, 'stock' => 15, 'allergens' => ['gluten', 'lait', 'œufs'], 'desc' => 'Deux tartes au citron pour le prix d\'une.'],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'product_type_id' => $p['type'],
                    'merchant_id' => $merchant->id,
                    'name' => $p['name'],
                    'slug' => Str::slug($p['name']),
                    'description' => $p['desc'],
                    'price' => $p['price'],
                    'stock_quantity' => $p['stock'],
                    'low_stock_threshold' => 5,
                    'allergens' => $p['allergens'],
                    'is_active' => true,
                    'is_customizable' => false,
                ]
            );
        }
    }
}