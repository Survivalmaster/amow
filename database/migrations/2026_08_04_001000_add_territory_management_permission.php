<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['slug' => 'territory-management'],
            [
                'name' => 'Territory Management',
                'description' => 'Can classify, claim, and administer World of Plastica territory tiles.',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-map',
                'icon_color' => '#a9d6e5',
                'icon_tooltip' => 'Territory Management',
                'grants_admin_access' => false,
                'admin_sections' => json_encode([]),
                'sort_order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('slug', 'territory-management')->delete();
    }
};
