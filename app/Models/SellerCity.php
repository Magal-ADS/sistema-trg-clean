<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerCity extends Model
{
    protected $fillable = ['glide_id', 'seller_name', 'seller_email', 'seller_phone', 'city', 'state', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'metadata' => 'array'];
    }
}
