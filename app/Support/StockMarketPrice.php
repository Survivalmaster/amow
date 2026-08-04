<?php

namespace App\Support;

class StockMarketPrice
{
    public const MIN_PRICE = 0.01;

    public static function clamp(float $price): float
    {
        return max(self::MIN_PRICE, round($price, 2));
    }
}
