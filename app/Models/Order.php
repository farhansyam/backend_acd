<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'user_phone_id',
        'address_id',
        'bp_id',
        'coupon_id',
        'scheduled_date',
        'scheduled_time',
        'apartment_surcharge',
        'discount_amount',
        'subtotal',
        'total_amount',
        'payment_method',
        'payment_status',
        'tripay_reference',
        'tripay_payment_url',
        'paid_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'scheduled_date'     => 'date',
        'apartment_surcharge' => 'decimal:2',
        'subtotal'           => 'decimal:2',
        'total_amount'       => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function phone(): BelongsTo
    {
        return $this->belongsTo(UserPhone::class, 'user_phone_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'bp_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Technician::class);
    }
    public function assignment(): HasOne
    {
        return $this->hasOne(\App\Models\OrderAssignment::class);
    }
}
