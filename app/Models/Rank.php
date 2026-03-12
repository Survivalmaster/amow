<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'order_index',
        'is_military',
    ];

    protected function casts(): array
    {
        return [
            'is_military' => 'boolean',
        ];
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }
}
