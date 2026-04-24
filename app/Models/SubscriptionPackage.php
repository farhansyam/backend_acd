<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'type',
        'name',
        'interval_months',
        'total_sessions',
        'price_multiplier',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price_multiplier' => 'float',
        'is_active'        => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
