<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'discount_percent',
        'max_discount',
        'min_order',
        'all_services',
        'valid_from',
        'valid_until',
        'max_usage_per_user',
        'is_active',
    ];

    protected $casts = [
        'discount_percent'   => 'decimal:2',
        'max_discount'       => 'decimal:2',
        'min_order'          => 'decimal:2',
        'all_services'       => 'boolean',
        'is_active'          => 'boolean',
        'valid_from'         => 'date',
        'valid_until'        => 'date',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(BpService::class, 'coupon_services', 'coupon_id', 'bp_service_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    // ─── Cek apakah kupon valid untuk user & order ────────────
    public function isValidForUser(int $userId, float $orderTotal): array
    {
        // Aktif?
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Kupon tidak aktif.'];
        }

        // Masa berlaku
        $today = Carbon::today();
        if ($today->lt($this->valid_from) || $today->gt($this->valid_until)) {
            return ['valid' => false, 'message' => 'Kupon sudah kadaluarsa atau belum berlaku.'];
        }

        // Minimal order
        if ($orderTotal < (float) $this->min_order) {
            return [
                'valid'   => false,
                'message' => 'Minimal order Rp ' . number_format($this->min_order, 0, ',', '.') . ' untuk menggunakan kupon ini.',
            ];
        }

        // Batas pemakaian per user
        $usageCount = $this->usages()->where('user_id', $userId)->count();
        if ($usageCount >= $this->max_usage_per_user) {
            return ['valid' => false, 'message' => 'Kupon sudah pernah digunakan.'];
        }

        // Hitung diskon
        $discountAmount = $orderTotal * ((float) $this->discount_percent / 100);
        if ($this->max_discount !== null) {
            $discountAmount = min($discountAmount, (float) $this->max_discount);
        }

        return [
            'valid'           => true,
            'discount_amount' => round($discountAmount),
            'message'         => "Diskon {$this->discount_percent}% berhasil diterapkan!",
        ];
    }
}
