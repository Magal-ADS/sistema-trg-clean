<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = ['glide_id', 'code', 'description', 'type', 'value', 'minimum_order_value', 'usage_limit', 'used_count', 'starts_at', 'expires_at', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_order_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
