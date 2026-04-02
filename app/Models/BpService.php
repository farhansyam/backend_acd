<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpService extends Model
{
    protected $fillable = [
        'bp_id',
        'service_type_id',
        'base_service',
        'discount',
        'is_active',
        'banner',
    ];

    protected $casts = [
        'base_service' => 'decimal:2',
        'discount'     => 'decimal:2',
        'is_active'    => 'integer',
    ];

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'bp_id');
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}
