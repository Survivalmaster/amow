<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel_id')->unique();
            $table->string('webhook_url');
            $table->string('embed_color', 7)->default('#C65B3F');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasTable('discord_settings')) {
            $settings = DB::table('discord_settings')->first();

            if ($settings && ! empty($settings->wpnn_webhook_url)) {
                DB::table('discord_webhooks')->insert([
                    'name' => 'WPNN',
                    'channel_id' => 'replace-me',
                    'webhook_url' => $settings->wpnn_webhook_url,
                    'embed_color' => $settings->wpnn_embed_color ?: '#C65B3F',
                    'is_active' => (bool) ($settings->wpnn_enabled ?? false),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_webhooks');
    }
};
