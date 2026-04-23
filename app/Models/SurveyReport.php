<?php
// app/Models/SurveyReport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyReport extends Model
{
    protected $fillable = [
        'order_id',
        'technician_id',
        'kondisi_unit',
        'bagian_bermasalah',
        'catatan',
        'rekomendasi',
        'photo_before',
        'photo_after',
        'customer_response',
        'responded_at',
    ];

    protected $casts = [
        'bagian_bermasalah' => 'array',
        'responded_at'      => 'datetime',
    ];

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function technician(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technician::class);
    }
}
