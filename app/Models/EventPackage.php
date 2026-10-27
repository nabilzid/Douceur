<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'merchant_id', 'name', 'slug', 'description', 'base_price',
        'min_quantity', 'max_quantity', 'includes', 'image_path', 'is_active',
    ];

    protected $casts = [
        'includes' => 'array',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(EventPackageItem::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'event_package_items')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}