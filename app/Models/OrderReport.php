<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReport extends Model
{
    protected $fillable = [
        'order_id',
        'technician_id',
        'photo_before',
        'photo_after',
        'notes',
        'filter_cleaned',
        'freon_checked',
        'drain_cleaned',
        'electrical_checked',
    ];

    protected $casts = [
        'filter_cleaned'      => 'boolean',
        'freon_checked'       => 'boolean',
        'drain_cleaned'       => 'boolean',
        'electrical_checked'  => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
