<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference', 'user_id', 'merchant_id', 'customer_name', 'customer_email',
        'customer_phone', 'event_date', 'event_type', 'guests_count', 'description',
        'budget', 'quoted_price', 'merchant_response', 'quote_pdf_path', 'amount_paid', 'deposit_invoice_path', 'balance_invoice_path', 'status',
        // ✅ Champs livraison
        'delivery_type', 'delivery_address', 'delivery_postal_code', 'delivery_city', 'delivery_notes',
        // ✅ Pourcentage de paiement (20 / 50 / 100)
        'payment_percentage',
    ];

    protected $casts = [
        'event_date' => 'date',
        'budget' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        // ✅ Ajouts (facultatifs mais utiles)
        'payment_percentage' => 'integer',
    ];


    public const EVENT_TYPES = [
        'birthday' => 'Anniversaire',
        'wedding' => 'Mariage',
        'baptism' => 'Baptême',
        'corporate' => 'Entreprise',
        'other' => 'Autre',
    ];

    public function eventLabel(): string
    {
        return self::EVENT_TYPES[$this->event_type] ?? $this->event_type;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomOrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ✅ Helpers livraison
    public function isDelivery(): bool
    {
        return $this->delivery_type === 'delivery';
    }

    public function isPickup(): bool
    {
        return $this->delivery_type === 'pickup';
    }
}