<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'delivery.min_order_amount',   'value' => '20',   'type' => 'decimal', 'group' => 'delivery', 'label' => 'Montant minimum pour livraison (€)'],
            ['key' => 'delivery.default_fee',        'value' => '5',    'type' => 'decimal', 'group' => 'delivery', 'label' => 'Frais de livraison par défaut (€)'],
            ['key' => 'delivery.free_threshold',     'value' => '50',   'type' => 'decimal', 'group' => 'delivery', 'label' => 'Seuil de livraison gratuite (€)'],
            ['key' => 'shop.name',                   'value' => 'Douceur', 'type' => 'string', 'group' => 'general', 'label' => 'Nom de la boutique'],
            ['key' => 'shop.email',                  'value' => 'contact@douceur.test', 'type' => 'string', 'group' => 'general', 'label' => 'Email de contact'],
            ['key' => 'shop.phone',                  'value' => '+33 1 23 45 67 89', 'type' => 'string', 'group' => 'general', 'label' => 'Téléphone'],
            ['key' => 'tax.vat_rate',                'value' => '5.5',  'type' => 'decimal', 'group' => 'tax', 'label' => 'Taux de TVA pâtisserie (%)'],
            ['key' => 'custom_order.min_lead_days',  'value' => '3',    'type' => 'integer', 'group' => 'custom_order', 'label' => 'Délai minimum commande sur mesure (jours)'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}