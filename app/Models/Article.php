<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'content',
        'type',
        'image',
        'color_hex',
        'is_active',
        'expired_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expired_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }

    public function isExpired(): bool
    {
        return $this->expired_at && now()->gt($this->expired_at);
    }

    public function getTypeLabel(): string
    {
        return $this->type === 'promo' ? 'Promo' : 'Tips';
    }
}
