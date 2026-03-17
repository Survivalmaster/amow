<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->foreignId('account_icon_id')->nullable()->constrained('account_icons')->nullOnDelete();
            $table->boolean('grants_admin_access')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['permission_id', 'user_id']);
        });

        $now = now();

        DB::table('permissions')->insert([
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to the admin area and admin actions.',
                'grants_admin_access' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Developer account badge.',
                'grants_admin_access' => false,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Game Master',
                'slug' => 'game-master',
                'description' => 'Game master account badge.',
                'grants_admin_access' => false,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Moderator account badge.',
                'grants_admin_access' => false,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $adminPermissionId = DB::table('permissions')->where('slug', 'admin')->value('id');
        $adminUserIds = DB::table('users')->where('is_admin', true)->pluck('id');

        foreach ($adminUserIds as $userId) {
            DB::table('permission_user')->updateOrInsert(
                ['permission_id' => $adminPermissionId, 'user_id' => $userId],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');
    }
};
