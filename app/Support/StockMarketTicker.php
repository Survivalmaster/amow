<?php

namespace App\Support;

use App\Models\Company;
use App\Models\StockMarketSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockMarketTicker
{
    private const MAX_UPWARD_TICK_PERCENT = 10.0;
    private const LOW_PRICE_RECOVERY_THRESHOLD = 5.0;
    private const MAX_RECOVERY_TICK_PERCENT = 35.0;

    public function fluctuateIfDue(): void
    {
        $latestUpdate = Company::query()->max('last_price_updated_at');

        if ($latestUpdate && Carbon::parse($latestUpdate)->gt(now()->subMinute())) {
            return;
        }

        DB::transaction(function () {
            $settings = StockMarketSetting::query()->firstOrCreate(
                ['id' => 1],
                [
                    'min_change_percent' => -3,
                    'max_change_percent' => 3,
                    'passive_growth_bias_percent' => 2,
                    'low_price_recovery_percent' => 30,
                ]
            );

            Company::query()->lockForUpdate()->get()->each(function (Company $company) use ($settings) {
                if ($company->last_price_updated_at && $company->last_price_updated_at->gt(now()->subMinute())) {
                    return;
                }

                $minChangePercent = (float) $settings->min_change_percent;
                $maxChangePercent = min((float) $settings->max_change_percent, self::MAX_UPWARD_TICK_PERCENT);
                $minBasisPoints = (int) round(min($minChangePercent, $maxChangePercent) * 100);
                $maxBasisPoints = (int) round($maxChangePercent * 100);

                $basisPoints = random_int($minBasisPoints, $maxBasisPoints);
                $randomChangePercent = $basisPoints / 100;
                $passiveGrowthPercent = max(0, (float) ($settings->passive_growth_bias_percent ?? 0));
                $lowPriceRecoveryPercent = $this->lowPriceRecoveryPercent(
                    (float) $company->current_price,
                    max(0, (float) ($settings->low_price_recovery_percent ?? 0))
                );
                $totalChangePercent = $randomChangePercent + $passiveGrowthPercent + $lowPriceRecoveryPercent;

                if ($totalChangePercent > 0) {
                    $totalChangePercent = min($totalChangePercent, $lowPriceRecoveryPercent > 0 ? self::MAX_RECOVERY_TICK_PERCENT : self::MAX_UPWARD_TICK_PERCENT);
                }

                $multiplier = 1 + ($totalChangePercent / 100);
                $nextPrice = StockMarketPrice::clamp((float) $company->current_price * $multiplier);

                $company->update([
                    'current_price' => $nextPrice,
                    'last_price_updated_at' => now(),
                ]);
            });
        });
    }

    private function lowPriceRecoveryPercent(float $price, float $configuredRecoveryPercent): float
    {
        if ($price >= self::LOW_PRICE_RECOVERY_THRESHOLD || $configuredRecoveryPercent <= 0) {
            return 0.0;
        }

        $recoveryWeight = (self::LOW_PRICE_RECOVERY_THRESHOLD - max(0, $price)) / self::LOW_PRICE_RECOVERY_THRESHOLD;

        return $configuredRecoveryPercent * $recoveryWeight;
    }
}
