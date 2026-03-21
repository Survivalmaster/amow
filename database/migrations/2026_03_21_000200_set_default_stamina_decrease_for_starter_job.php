<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('game_jobs')
            ->where('slug', 'begger')
            ->where(function ($query) {
                $query->whereNull('stamina_decrease')
                    ->orWhere('stamina_decrease', 0);
            })
            ->update([
                'stamina_decrease' => 10,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('game_jobs')
            ->where('slug', 'begger')
            ->where('stamina_decrease', 10)
            ->update([
                'stamina_decrease' => 0,
                'updated_at' => now(),
            ]);
    }
};
