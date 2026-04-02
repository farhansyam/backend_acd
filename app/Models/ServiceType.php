<?php

namespace App\Models;

use App\Models\BpService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'integer',
    ];

    public function bpServices(): HasMany
    {
        return $this->hasMany(BpService::class, 'service_type_id');
    }
}
