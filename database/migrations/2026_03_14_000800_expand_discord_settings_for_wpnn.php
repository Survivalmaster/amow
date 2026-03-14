<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_settings', function (Blueprint $table) {
            $table->text('wpnn_message_prefix')->nullable()->after('wpnn_webhook_url');
            $table->string('wpnn_author_name')->nullable()->after('wpnn_message_prefix');
            $table->string('wpnn_author_icon_url')->nullable()->after('wpnn_author_name');
            $table->string('wpnn_thumbnail_url')->nullable()->after('wpnn_author_icon_url');
            $table->boolean('wpnn_show_timestamp')->default(true)->after('wpnn_footer_text');
        });
    }

    public function down(): void
    {
        Schema::table('discord_settings', function (Blueprint $table) {
            $table->dropColumn([
                'wpnn_message_prefix',
                'wpnn_author_name',
                'wpnn_author_icon_url',
                'wpnn_thumbnail_url',
                'wpnn_show_timestamp',
            ]);
        });
    }
};
