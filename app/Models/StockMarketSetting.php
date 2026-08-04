<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMarketSetting extends Model
{
    protected $fillable = [
        'min_change_percent',
        'max_change_percent',
        'passive_growth_bias_percent',
        'low_price_recovery_percent',
        'low_price_minimum_lift',
        'buy_impact_percent_per_100_shares',
        'sell_impact_percent_per_100_shares',
        'max_trade_impact_percent',
        'crash_trade_threshold_shares',
        'crash_extra_percent',
    ];

    protected function casts(): array
    {
        return [
            'min_change_percent' => 'decimal:2',
            'max_change_percent' => 'decimal:2',
            'passive_growth_bias_percent' => 'decimal:2',
            'low_price_recovery_percent' => 'decimal:2',
            'low_price_minimum_lift' => 'decimal:2',
            'buy_impact_percent_per_100_shares' => 'decimal:2',
            'sell_impact_percent_per_100_shares' => 'decimal:2',
            'max_trade_impact_percent' => 'decimal:2',
            'crash_trade_threshold_shares' => 'integer',
            'crash_extra_percent' => 'decimal:2',
        ];
    }
}
