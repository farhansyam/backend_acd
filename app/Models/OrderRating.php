<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRating extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'technician_id',
        'rating',
        'review',
        'second_technician_id',
        'second_rating',
        'second_review',
    ];
    protected $casts = [
        'rating'        => 'integer',
        'second_rating' => 'integer',
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
}
