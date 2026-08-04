<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')
            ->where('slug', 'admin')
            ->orderBy('id')
            ->get()
            ->each(function ($permission) {
                $sections = json_decode($permission->admin_sections ?: '[]', true) ?: [];

                if (! in_array('nation_logs', $sections, true)) {
                    $sections[] = 'nation_logs';
                }

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update([
                        'admin_sections' => json_encode(array_values($sections)),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('slug', 'admin')
            ->orderBy('id')
            ->get()
            ->each(function ($permission) {
                $sections = json_decode($permission->admin_sections ?: '[]', true) ?: [];
                $sections = array_values(array_diff($sections, ['nation_logs']));

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update([
                        'admin_sections' => json_encode($sections),
                        'updated_at' => now(),
                    ]);
            });
    }
};
