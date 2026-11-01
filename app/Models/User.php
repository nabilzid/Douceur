<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'default_address_id', 'is_active',
        'email_verification_token', 'email_verification_sent_at',   // ← AJOUT
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',   // ← AJOUT (sécurité : ne pas exposer en JSON)
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'email_verification_sent_at' => 'datetime',   // ← AJOUT
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'default_address_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customOrders(): HasMany
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMerchant(): bool
    {
        return $this->role === 'merchant';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Envoie la notification de vérification email (style Douceur).
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification());
    }

    /**
     * Génère un nouveau token de vérification email.
     *
     * - Stocke le hash SHA-256 en base (pas le token en clair)
     * - Retourne le token en clair (à inclure dans l'URL du mail)
     */
    public function generateEmailVerificationToken(): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'email_verification_token' => hash('sha256', $token),
            'email_verification_sent_at' => now(),
        ])->save();

        return $token;
    }
}