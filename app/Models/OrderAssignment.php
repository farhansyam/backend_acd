<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAssignment extends Model
{
    protected $fillable = [
        'order_id',
        'technician_id',
        'assigned_by',
        'status',
        'assigned_at',
        'accepted_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'accepted_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
