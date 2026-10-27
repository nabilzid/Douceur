<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = [
        'name', 'postal_codes', 'delivery_fee', 'free_delivery_threshold',
        'min_order_amount', 'estimated_delivery_minutes', 'is_active',
    ];

    protected $casts = [
        'postal_codes' => 'array',
        'delivery_fee' => 'decimal:2',
        'free_delivery_threshold' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(DeliverySlot::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}