<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('changelogs', function (Blueprint $table) {
            $table->json('added_features')->nullable()->after('features');
            $table->json('edited_features')->nullable()->after('added_features');
            $table->json('removed_features')->nullable()->after('edited_features');
        });

        DB::table('changelogs')
            ->whereNotNull('features')
            ->update([
                'added_features' => DB::raw('features'),
            ]);
    }

    public function down(): void
    {
        Schema::table('changelogs', function (Blueprint $table) {
            $table->dropColumn(['added_features', 'edited_features', 'removed_features']);
        });
    }
};
