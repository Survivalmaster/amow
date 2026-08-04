<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stock_market_settings')->update([
            'crash_trade_threshold_shares' => 100,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('stock_market_settings')->update([
            'crash_trade_threshold_shares' => 500,
            'updated_at' => now(),
        ]);
    }
};
