<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('icon_class')->nullable()->after('type');
            $table->unsignedInteger('inventory_slot_bonus')->default(0)->after('is_home');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['icon_class', 'inventory_slot_bonus']);
        });
    }
};
