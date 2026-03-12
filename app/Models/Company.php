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
        'description',
    ];

    public function holdings(): HasMany
    {
        return $this->hasMany(StockHolding::class);
    }
}
