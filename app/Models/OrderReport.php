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
        'unit_installed',
        'piping_neat',
        'cooling_test',
        'remote_working',
        'ac_dismantled',
        'unit_safe_transport',
    ];

    protected $casts = [
        'filter_cleaned'      => 'boolean',
        'freon_checked'       => 'boolean',
        'drain_cleaned'       => 'boolean',
        'electrical_checked'  => 'boolean',
        'unit_installed'  => 'boolean',
        'piping_neat'  => 'boolean',
        'cooling_test'  => 'boolean',
        'remote_working'  => 'boolean',
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
