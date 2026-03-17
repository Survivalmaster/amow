<?php

namespace App\Support;

use App\Models\Company;
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
            Company::query()->lockForUpdate()->get()->each(function (Company $company) {
                if ($company->last_price_updated_at && $company->last_price_updated_at->gt(now()->subMinute())) {
                    return;
                }

                $multiplier = random_int(97, 103) / 100;
                $nextPrice = max(5, round((float) $company->current_price * $multiplier, 2));

                $company->update([
                    'current_price' => $nextPrice,
                    'last_price_updated_at' => now(),
                ]);
            });
        });
    }
}
