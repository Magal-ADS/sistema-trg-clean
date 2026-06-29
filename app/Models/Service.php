<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['glide_id', 'name', 'slug', 'description', 'price', 'image_url', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean', 'metadata' => 'array'];
    }
}
