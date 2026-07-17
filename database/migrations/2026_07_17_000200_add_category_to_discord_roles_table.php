<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discord_roles', function (Blueprint $table) {
            $table->string('category')->nullable()->after('is_managed');
        });
    }

    public function down(): void
    {
        Schema::table('discord_roles', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
