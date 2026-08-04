<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'current_price',
        'max_shares_per_character',
        'last_price_updated_at',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:2',
            'max_shares_per_character' => 'integer',
            'last_price_updated_at' => 'datetime',
        ];
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(StockHolding::class);
    }
}
