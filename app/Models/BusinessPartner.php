<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // tambah ini

class BusinessPartner extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'city',
        'provience',
        'address',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bpServices(): HasMany
    {
        return $this->hasMany(BpService::class, 'bp_id');
    }

    public function technicians(): HasMany
    {
        return $this->hasMany(Technician::class, 'bp_id');
    }

    public function pendingTechnicians(): HasMany
    {
        return $this->hasMany(Technician::class, 'bp_id')->where('status', 'pending');
    }
}
