<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'property_type',
        'label',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'district_id',
        'district_name',
        'village_id',
        'village_name',
        'full_address',
        'latitude',
        'longitude',
        'is_primary',
    ];

    protected $casts = [
        'is_primary'  => 'boolean',
        'latitude'    => 'float',
        'longitude'   => 'float',
    ];

    // ─── Relasi ───────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Accessor: alamat lengkap terformat ──────────────────
    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->full_address,
            $this->village_name,
            $this->district_name,
            $this->city_name,
            $this->province_name,
        ]);

        return implode(', ', $parts);
    }
}
