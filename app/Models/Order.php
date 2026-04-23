<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\OrderReport;

use function PHPSTORM_META\map;

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
        'warranty_started_at',
        'relocation_type',
        'origin_address_id',
        'transport_fee',
        'split_technician',
        'second_technician_id',
        'order_type',
        'keluhan',
        'keluhan_lainnya',
        'is_perbaikan'
    ];

    protected $casts = [
        'scheduled_date'      => 'date',
        'warranty_expires_at' => 'datetime', // ← tambah
        'warranty_started_at' => 'datetime', // ← tambah
        'auto_complete_at'    => 'datetime', // ← tambah kalau belum ada
        'paid_at'             => 'datetime', // ← tambah kalau belum ada
        'split_technician' => 'boolean',
        'transport_fee'    => 'decimal:2',
        'keluhan'      => 'array',

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

    public function originAddress()
    {
        return $this->belongsTo(Address::class, 'origin_address_id');
    }

    public function secondTechnician()
    {
        return $this->belongsTo(Technician::class, 'second_technician_id');
    }

    public function surveyReport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SurveyReport::class);
    }

    public function phase2Order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'phase2_order_id');
    }

    public function surveyOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class, 'survey_order_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'                   => 'Menunggu Konfirmasi',
            'pending_transport_fee'     => 'Menunggu Biaya Transportasi',
            'pending_transport_fee_set' => 'Konfirmasi Biaya Transportasi',
            'confirmed'                 => 'Dikonfirmasi',
            'in_progress'               => 'Sedang Dikerjakan',
            'waiting_confirmation'      => 'Menunggu Konfirmasi Customer',
            'warranty'                  => 'Masa Garansi',
            'complained'                => 'Dikomplain',
            'completed'                 => 'Selesai',
            'disassembled' => 'Sudah Dibongkar, Menunggu Pemasangan',
            'cancelled'                 => 'Dibatalkan',
            default                     => $this->status,
        };
    }
}
