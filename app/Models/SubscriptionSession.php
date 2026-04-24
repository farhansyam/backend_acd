<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SubscriptionSession extends Model
{
    protected $fillable = [
        'subscription_id',
        'session_number',
        'scheduled_date',
        'scheduled_time',
        'technician_id',
        'status',
        'auto_complete_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_date'  => 'date',
        'auto_complete_at' => 'datetime',
        'completed_at'    => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function report(): HasOne
    {
        return $this->hasOne(SubscriptionSessionReport::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeConfirmedByCustomer(): bool
    {
        return $this->status === 'waiting_confirmation';
    }
}
