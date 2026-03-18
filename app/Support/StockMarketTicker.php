<?php

namespace App\Support;

use App\Models\Company;
use App\Models\StockMarketSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockMarketTicker
{
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

                $basisPoints = random_int(
                    (int) round(((float) $settings->min_change_percent) * 100),
                    (int) round(((float) $settings->max_change_percent) * 100)
                );
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
