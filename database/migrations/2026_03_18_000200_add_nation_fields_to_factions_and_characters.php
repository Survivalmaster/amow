<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factions', function (Blueprint $table) {
            $table->unsignedInteger('nation_bank_credits')->default(0)->after('color');
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->boolean('is_nation_leader')->default(false)->after('is_business_owner');
        });

        DB::table('characters')
            ->select('id')
            ->orderBy('faction_id')
            ->orderByDesc('military_score')
            ->orderBy('id')
            ->get()
            ->groupBy('faction_id')
            ->each(function ($characters) {
                $leaderId = $characters->first()?->id;

                if ($leaderId) {
                    DB::table('characters')->where('id', $leaderId)->update(['is_nation_leader' => true]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('is_nation_leader');
        });

        Schema::table('factions', function (Blueprint $table) {
            $table->dropColumn('nation_bank_credits');
        });
    }
};
