<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'glide_id',
        'category_id',
        'sub_category_id',
        'size_category_id',
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'promotional_price',
        'has_variation',
        'is_featured',
        'is_best_seller',
        'is_unavailable',
        'stock',
        'image_url',
        'images',
        'variations',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promotional_price' => 'decimal:2',
            'has_variation' => 'boolean',
            'is_featured' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_unavailable' => 'boolean',
            'images' => 'array',
            'variations' => 'array',
            'metadata' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
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
