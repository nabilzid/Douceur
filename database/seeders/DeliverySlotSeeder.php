<?php

namespace Database\Seeders;

use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliverySlotSeeder extends Seeder
{
    public function run(): void
    {
        $zone = DeliveryZone::where('name', 'Paris intra-muros')->first();

        for ($i = 1; $i <= 7; $i++) {
            $date = now()->addDays($i)->toDateString();

            // Créneaux de livraison
            foreach ([['10:00','12:00'], ['14:00','16:00'], ['16:00','18:00']] as [$start, $end]) {
                DeliverySlot::updateOrCreate(
                    ['date' => $date, 'start_time' => $start, 'end_time' => $end, 'type' => 'delivery', 'delivery_zone_id' => $zone?->id],
                    ['max_orders' => 10, 'current_orders' => 0, 'is_active' => true]
                );
            }

            // Créneaux de retrait
            foreach ([['09:00','12:00'], ['14:00','19:00']] as [$start, $end]) {
                DeliverySlot::updateOrCreate(
                    ['date' => $date, 'start_time' => $start, 'end_time' => $end, 'type' => 'pickup', 'delivery_zone_id' => null],
                    ['max_orders' => 20, 'current_orders' => 0, 'is_active' => true]
                );
            }
        }
    }
}