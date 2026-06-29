<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FragranceType extends Model
{
    protected $fillable = ['glide_id', 'name', 'slug', 'description', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function variationPrices(): HasMany
    {
        return $this->hasMany(SizeFragrancePrice::class);
    }
}
