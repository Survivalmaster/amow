<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_webhooks', function (Blueprint $table) {
            $table->string('command_name')->nullable()->unique()->after('name');
            $table->string('command_description')->nullable()->after('command_name');
            $table->string('access_mode')->default('anyone')->after('embed_color');
            $table->string('role_id')->nullable()->after('access_mode');
        });
    }

    public function down(): void
    {
        Schema::table('discord_webhooks', function (Blueprint $table) {
            $table->dropColumn([
                'command_name',
                'command_description',
                'access_mode',
                'role_id',
            ]);
        });
    }
};
