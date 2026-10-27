<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'label', 'recipient_name', 'phone', 'street', 'street_2',
        'postal_code', 'city', 'country', 'is_default', 'type',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return trim("{$this->street} {$this->street_2}, {$this->postal_code} {$this->city}, {$this->country}");
    }
}