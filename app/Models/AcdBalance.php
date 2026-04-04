<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcdBalance extends Model
{
    protected $fillable = [
        'balance',
        'total_earned',
        'total_withdrawn',
    ];

    protected $casts = [
        'balance'          => 'decimal:2',
        'total_earned'     => 'decimal:2',
        'total_withdrawn'  => 'decimal:2',
    ];

    // Ambil atau buat record ACD balance (singleton)
    public static function getInstance(): self
    {
        return self::firstOrCreate([], [
            'balance'         => 0,
            'total_earned'    => 0,
            'total_withdrawn' => 0,
        ]);
    }
}
