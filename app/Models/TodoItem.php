<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TodoItem extends Model
{
    protected $fillable = ['glide_id', 'title', 'description', 'status', 'priority', 'due_at', 'metadata'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'metadata' => 'array'];
    }
}
