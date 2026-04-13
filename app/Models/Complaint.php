<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'bp_id',
        'technician_id',
        'rework_technician_id',
        'assigned_by',
        'title',
        'description',
        'photo',
        'bp_comment',
        'status',
        'rework_cost',
        'rework_earning',
        'warranty_expires_at',
        'resolved_at',
    ];

    protected $casts = [
        'warranty_expires_at' => 'datetime',
        'resolved_at'         => 'datetime',
        'rework_cost'         => 'float',
        'rework_earning'      => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function reworkTechnician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'rework_technician_id');
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'bp_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isWarrantyActive(): bool
    {
        return $this->warranty_expires_at && now()->lt($this->warranty_expires_at);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'             => 'Menunggu Tinjauan',
            'in_review'        => 'Sedang Ditinjau',
            'rework_assigned'  => 'Teknisi Ditugaskan',
            'rework_completed' => 'Rework Selesai',
            'closed'           => 'Selesai',
            default            => $this->status,
        };
    }
}
