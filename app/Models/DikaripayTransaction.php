<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DikaripayTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'order_id',
        'tripay_reference',
        'payment_method',
        'status',
        'description',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
