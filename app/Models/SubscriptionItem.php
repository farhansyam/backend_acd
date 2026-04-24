<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionItem extends Model
{
    protected $fillable = [
        'subscription_id',
        'bp_service_id',
        'quantity',
        'unit_price',
        'subtotal_per_session',
        'subtotal_total',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function bpService(): BelongsTo
    {
        return $this->belongsTo(BpService::class);
    }
}
