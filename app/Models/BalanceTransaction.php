<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BalanceTransaction extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'order_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'status',
        'release_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
        'release_at'     => 'datetime',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
