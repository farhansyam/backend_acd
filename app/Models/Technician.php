<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Technician extends Model
{
    protected $fillable = [
        'user_id',
        'bp_id',
        'province',
        'city',
        'districts',
        'address',
        'grade',
        'skck_file',
        'ktp_photo',
        'selfie_photo',
        'certificate',
        'extra_doc_1',
        'extra_doc_2',
        'extra_note',
        'status',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'districts'   => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'bp_id');
    }

    // Helper status
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function balanceTransactions()
    {
        return $this->morphMany(\App\Models\BalanceTransaction::class, 'owner');
    }
}
