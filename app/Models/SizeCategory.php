<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SizeCategory extends Model
{
    protected $fillable = ['glide_id', 'name', 'slug', 'description', 'sort_order', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(Size::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
