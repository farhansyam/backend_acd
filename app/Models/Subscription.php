<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'address_id',
        'user_phone_id',
        'bp_id',
        'subscription_package_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'tripay_reference',
        'tripay_payment_url',
        'paid_at',
        'starts_at',
        'expires_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'paid_at'    => 'datetime',
        'starts_at'  => 'date',
        'expires_at' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function userPhone(): BelongsTo
    {
        return $this->belongsTo(UserPhone::class);
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'bp_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(SubscriptionSession::class)->orderBy('session_number');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function completedSessionsCount(): int
    {
        return $this->sessions()->where('status', 'completed')->count();
    }

    public function nextPendingSession(): ?SubscriptionSession
    {
        return $this->sessions()
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress', 'waiting_confirmation'])
            ->orderBy('session_number')
            ->first();
    }
}
