<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissionId = DB::table('permissions')->where('slug', 'nation-leader')->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'Nation Leader',
                'slug' => 'nation-leader',
                'description' => 'Nation leadership badge and requisition access.',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-star',
                'icon_color' => '#f4d77a',
                'icon_tooltip' => 'Nation Leader',
                'grants_admin_access' => false,
                'admin_sections' => json_encode([]),
                'sort_order' => 50,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $leaderUserIds = DB::table('characters')
            ->where('is_nation_leader', true)
            ->pluck('user_id');

        foreach ($leaderUserIds as $userId) {
            DB::table('permission_user')->updateOrInsert(
                ['permission_id' => $permissionId, 'user_id' => $userId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('slug', 'nation-leader')->value('id');

        if ($permissionId) {
            DB::table('permission_user')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
    }
};
