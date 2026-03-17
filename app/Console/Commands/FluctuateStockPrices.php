<?php

namespace App\Console\Commands;

use App\Support\StockMarketTicker;
use Illuminate\Console\Command;

class FluctuateStockPrices extends Command
{
    public function __construct(private readonly StockMarketTicker $ticker)
    {
        parent::__construct();
    }

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
        $this->ticker->fluctuateIfDue();

        $this->info('Stock prices updated.');
    }
}
