<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stock_market_settings')->update([
            'max_trade_impact_percent' => 99,
            'crash_extra_percent' => 99,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('stock_market_settings')->update([
            'max_trade_impact_percent' => 12,
            'crash_extra_percent' => 4,
            'updated_at' => now(),
        ]);
    }
};
