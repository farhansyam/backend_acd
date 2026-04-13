<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\OrderReport;

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
        'technician_id',
        'warranty_expires_at',
        'warranty_started_at'
    ];

    protected $casts = [
        'scheduled_date'      => 'date',
        'warranty_expires_at' => 'datetime', // ← tambah
        'warranty_started_at' => 'datetime', // ← tambah
        'auto_complete_at'    => 'datetime', // ← tambah kalau belum ada
        'paid_at'             => 'datetime', // ← tambah kalau belum ada
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

    public function report(): HasOne
    {
        return $this->hasOne(OrderReport::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(\App\Models\OrderRating::class);
    }
    public function complaint(): HasOne
    {
        return $this->hasOne(\App\Models\Complaint::class);
    }
}
