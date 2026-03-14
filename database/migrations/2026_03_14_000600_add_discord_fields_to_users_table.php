<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('discord_user_id')->nullable()->unique()->after('email');
            $table->string('discord_username')->nullable()->after('discord_user_id');
            $table->string('discord_avatar')->nullable()->after('discord_username');
            $table->string('discord_link_token', 64)->nullable()->unique()->after('discord_avatar');
            $table->timestamp('discord_link_token_expires_at')->nullable()->after('discord_link_token');
            $table->timestamp('discord_linked_at')->nullable()->after('discord_link_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'discord_user_id',
                'discord_username',
                'discord_avatar',
                'discord_link_token',
                'discord_link_token_expires_at',
                'discord_linked_at',
            ]);
        });
    }
};
