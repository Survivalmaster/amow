<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class FluctuateStockPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amow:fluctuate-stock-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Randomly adjust stock prices for Plastica companies.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Company::query()->each(function (Company $company) {
            $multiplier = random_int(92, 109) / 100;
            $company->update([
                'current_price' => max(5, round($company->current_price * $multiplier, 2)),
            ]);
        });

        $this->info('Stock prices updated.');
    }
}
