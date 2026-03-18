<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_market_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_change_percent', 5, 2)->default(-3);
            $table->decimal('max_change_percent', 5, 2)->default(3);
            $table->timestamps();
        });

        DB::table('stock_market_settings')->insert([
            'id' => 1,
            'min_change_percent' => -3,
            'max_change_percent' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_market_settings');
    }
};
