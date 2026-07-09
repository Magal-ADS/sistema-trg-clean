<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerDailyEntry extends Model
{
    protected $fillable = [
        'seller_account_id',
        'entry_date',
        'presential_count',
        'instagram_count',
        'whatsapp_count',
        'sales_count',
        'sales_total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'sales_total' => 'decimal:2',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerAccount::class, 'seller_account_id');
    }
}
