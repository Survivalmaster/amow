<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMarketSetting extends Model
{
    protected $fillable = [
        'min_change_percent',
        'max_change_percent',
    ];

    protected function casts(): array
    {
        return [
            'min_change_percent' => 'decimal:2',
            'max_change_percent' => 'decimal:2',
        ];
    }
}
