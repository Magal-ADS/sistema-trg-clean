<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    protected $fillable = ['glide_id', 'size_category_id', 'name', 'slug', 'volume', 'unit', 'sort_order', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['volume' => 'decimal:2', 'is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function sizeCategory(): BelongsTo
    {
        return $this->belongsTo(SizeCategory::class);
    }

    public function variationPrices(): HasMany
    {
        return $this->hasMany(SizeFragrancePrice::class);
    }
}
