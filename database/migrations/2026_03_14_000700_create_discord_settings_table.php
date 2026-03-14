<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_settings', function (Blueprint $table) {
            $table->id();
            $table->string('wpnn_webhook_url')->nullable();
            $table->string('wpnn_embed_color', 7)->default('#c65b3f');
            $table->string('wpnn_footer_text')->default('WPNN desk');
            $table->boolean('wpnn_enabled')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discord_settings');
    }
};
