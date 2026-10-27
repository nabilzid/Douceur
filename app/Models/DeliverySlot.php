<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverySlot extends Model
{
    protected $fillable = [
        'delivery_zone_id', 'date', 'start_time', 'end_time', 'type',
        'max_orders', 'current_orders', 'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAvailable(): bool
    {
        return $this->is_active && $this->current_orders < $this->max_orders;
    }
}