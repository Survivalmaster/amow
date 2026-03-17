<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_icons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon_type')->default('fontawesome');
            $table->string('icon_value');
            $table->string('color')->nullable();
            $table->string('tooltip')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('account_icon_user', function (Blueprint $table) {
            $table->foreignId('account_icon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['account_icon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_icon_user');
        Schema::dropIfExists('account_icons');
    }
};
