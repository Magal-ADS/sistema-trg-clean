<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerAccount extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'city',
        'state',
        'password',
        'is_active',
        'metadata',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function dailyEntries(): HasMany
    {
        return $this->hasMany(SellerDailyEntry::class);
    }
}
