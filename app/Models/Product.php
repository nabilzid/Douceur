<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_type_id', 'merchant_id', 'name', 'slug', 'description',
        'price', 'stock_quantity', 'low_stock_threshold', 'allergens',
        'image_path', 'is_active', 'is_customizable',
    ];

    protected $casts = [
        'allergens' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_customizable' => 'boolean',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->morphOne(Media::class, 'mediable')->where('is_primary', true);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAlert()
    {
        return $this->hasOne(StockAlert::class);
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
}