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
            $table->decimal('low_price_minimum_lift', 8, 2)->default(0.25)->after('low_price_recovery_percent');
        });

        DB::table('stock_market_settings')->update([
            'low_price_minimum_lift' => 0.25,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('stock_market_settings', function (Blueprint $table) {
            $table->dropColumn('low_price_minimum_lift');
        });
    }
};
