<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSetting extends Model
{
    protected $fillable = ['key', 'label', 'value', 'is_active'];

    protected function casts(): array
    {
        return ['value' => 'array', 'is_active' => 'boolean'];
    }
}
