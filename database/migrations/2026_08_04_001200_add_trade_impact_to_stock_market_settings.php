<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_market_settings', function (Blueprint $table) {
            $table->decimal('buy_impact_percent_per_100_shares', 5, 2)->default(0.35)->after('max_change_percent');
            $table->decimal('sell_impact_percent_per_100_shares', 5, 2)->default(0.45)->after('buy_impact_percent_per_100_shares');
            $table->decimal('max_trade_impact_percent', 5, 2)->default(12)->after('sell_impact_percent_per_100_shares');
            $table->unsignedInteger('crash_trade_threshold_shares')->default(100)->after('max_trade_impact_percent');
            $table->decimal('crash_extra_percent', 5, 2)->default(4)->after('crash_trade_threshold_shares');
        });
    }

    public function down(): void
    {
        Schema::table('stock_market_settings', function (Blueprint $table) {
            $table->dropColumn([
                'buy_impact_percent_per_100_shares',
                'sell_impact_percent_per_100_shares',
                'max_trade_impact_percent',
                'crash_trade_threshold_shares',
                'crash_extra_percent',
            ]);
        });
    }
};
