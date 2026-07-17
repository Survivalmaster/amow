<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_roles', function (Blueprint $table) {
            $table->id();
            $table->string('discord_id')->unique();
            $table->string('name');
            $table->string('color', 20)->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_managed')->default(false);
            $table->unsignedInteger('member_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('discord_role_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discord_role_id')->constrained('discord_roles')->cascadeOnDelete();
            $table->string('discord_user_id');
            $table->string('username')->nullable();
            $table->string('display_name')->nullable();
            $table->string('avatar_url', 2048)->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['discord_role_id', 'discord_user_id']);
            $table->index('discord_user_id');
        });

        $now = now();
        $adminSections = DB::table('permissions')->where('slug', 'admin')->value('admin_sections');

        if ($adminSections) {
            $sections = json_decode($adminSections, true) ?: [];

            if (! in_array('discord_management', $sections, true)) {
                $sections[] = 'discord_management';

                DB::table('permissions')
                    ->where('slug', 'admin')
                    ->update([
                        'admin_sections' => json_encode(array_values($sections)),
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_role_members');
        Schema::dropIfExists('discord_roles');
    }
};
