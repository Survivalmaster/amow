<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_market_settings', function (Blueprint $table) {
            $table->decimal('passive_growth_bias_percent', 5, 2)->default(2)->after('max_change_percent');
            $table->decimal('low_price_recovery_percent', 5, 2)->default(30)->after('passive_growth_bias_percent');
        });

        DB::table('stock_market_settings')->update([
            'passive_growth_bias_percent' => 2,
            'low_price_recovery_percent' => 30,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('stock_market_settings', function (Blueprint $table) {
            $table->dropColumn(['passive_growth_bias_percent', 'low_price_recovery_percent']);
        });
    }
};
