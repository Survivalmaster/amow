<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stock_market_settings')
            ->where('max_change_percent', '>', 10)
            ->update([
                'max_change_percent' => 10,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
