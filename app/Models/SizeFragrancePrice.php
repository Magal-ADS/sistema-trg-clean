<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeFragrancePrice extends Model
{
    protected $fillable = ['glide_id', 'product_id', 'size_id', 'fragrance_type_id', 'color_type_id', 'price', 'promotional_price', 'stock', 'is_active', 'metadata'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'promotional_price' => 'decimal:2', 'is_active' => 'boolean', 'metadata' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function fragranceType(): BelongsTo
    {
        return $this->belongsTo(FragranceType::class);
    }

    public function colorType(): BelongsTo
    {
        return $this->belongsTo(ColorType::class);
    }
}
