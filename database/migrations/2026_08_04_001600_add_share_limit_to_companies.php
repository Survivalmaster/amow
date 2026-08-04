<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedInteger('max_shares_per_character')->nullable()->after('current_price');
        });

        DB::table('companies')->whereNull('max_shares_per_character')->update([
            'max_shares_per_character' => 1000,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('max_shares_per_character');
        });
    }
};
