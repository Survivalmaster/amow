<?php

namespace App\Support;

use App\Models\Company;
use App\Models\StockMarketSetting;

class StockMarketImpact
{
    private const MAX_BUY_IMPACT_PERCENT = 10.0;

    public function applyBuyImpact(Company $company, int $shares): array
    {
        return $this->applyImpact($company, $shares, 'buy');
    }

    public function applySellImpact(Company $company, int $shares): array
    {
        return $this->applyImpact($company, $shares, 'sell');
    }

    private function applyImpact(Company $company, int $shares, string $direction): array
    {
        $settings = StockMarketSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'min_change_percent' => -3,
                'max_change_percent' => 3,
                'passive_growth_bias_percent' => 2,
                'low_price_recovery_percent' => 30,
                'buy_impact_percent_per_100_shares' => 0.35,
                'sell_impact_percent_per_100_shares' => 0.45,
                'max_trade_impact_percent' => 99,
                'crash_trade_threshold_shares' => 100,
                'crash_extra_percent' => 99,
            ]
        );

        $priceBefore = (float) $company->current_price;
        $baseImpact = ($shares / 100) * (float) (
            $direction === 'buy'
                ? $settings->buy_impact_percent_per_100_shares
                : $settings->sell_impact_percent_per_100_shares
        );
        $baseImpact = min(
            $baseImpact,
            $direction === 'buy' ? self::MAX_BUY_IMPACT_PERCENT : (float) $settings->max_trade_impact_percent
        );

        $crashImpact = 0.0;

        if ($direction === 'sell' && $shares >= (int) $settings->crash_trade_threshold_shares) {
            $extraBlocks = floor($shares / max(1, (int) $settings->crash_trade_threshold_shares));
            $crashImpact = min(
                (float) $settings->max_trade_impact_percent,
                $extraBlocks * (float) $settings->crash_extra_percent
            );
        }

        $totalImpact = min(
            $direction === 'buy' ? self::MAX_BUY_IMPACT_PERCENT : (float) $settings->max_trade_impact_percent,
            $baseImpact + $crashImpact
        );
        $multiplier = $direction === 'buy'
            ? 1 + ($totalImpact / 100)
            : 1 - ($totalImpact / 100);
        $priceAfter = StockMarketPrice::clamp($priceBefore * $multiplier);

        $company->update([
            'current_price' => $priceAfter,
            'last_price_updated_at' => now(),
        ]);

        return [
            'price_before' => $priceBefore,
            'price_after' => $priceAfter,
            'impact_percent' => round($direction === 'buy' ? $totalImpact : -$totalImpact, 2),
            'crash_applied' => $crashImpact > 0,
        ];
    }
}
