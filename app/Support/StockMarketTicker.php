<?php

namespace App\Support;

use App\Models\Company;
use App\Models\StockMarketSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockMarketTicker
{
    private const MAX_UPWARD_TICK_PERCENT = 10.0;

    public function fluctuateIfDue(): void
    {
        $latestUpdate = Company::query()->max('last_price_updated_at');

        if ($latestUpdate && Carbon::parse($latestUpdate)->gt(now()->subMinute())) {
            return;
        }

        DB::transaction(function () {
            $settings = StockMarketSetting::query()->firstOrCreate(
                ['id' => 1],
                ['min_change_percent' => -3, 'max_change_percent' => 3]
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
                $multiplier = 1 + ($basisPoints / 10000);
                $nextPrice = max(5, round((float) $company->current_price * $multiplier, 2));

                $company->update([
                    'current_price' => $nextPrice,
                    'last_price_updated_at' => now(),
                ]);
            });
        });
    }
}
