<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('required_level')->nullable()->after('required_rank_id');
        });

        Schema::table('licences', function (Blueprint $table) {
            $table->unsignedInteger('required_level')->nullable()->after('required_rank_id');
        });

        $rankLevels = DB::table('ranks')->pluck('order_index', 'id');

        foreach (['items', 'licences'] as $table) {
            DB::table($table)
                ->whereNotNull('required_rank_id')
                ->orderBy('id')
                ->each(function (object $row) use ($rankLevels, $table) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'required_level' => $rankLevels[$row->required_rank_id] ?? null,
                            'required_rank_id' => null,
                        ]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('required_level');
        });

        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('required_level');
        });
    }
};
