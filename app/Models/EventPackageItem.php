<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPackageItem extends Model
{
    protected $fillable = ['event_package_id', 'product_id', 'quantity'];

    public function eventPackage(): BelongsTo
    {
        return $this->belongsTo(EventPackage::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}