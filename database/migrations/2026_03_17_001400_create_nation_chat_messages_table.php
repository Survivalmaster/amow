<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nation_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained()->cascadeOnDelete();
            $table->foreignId('faction_id')->constrained()->cascadeOnDelete();
            $table->string('message', 400);
            $table->timestamps();
            $table->index(['faction_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nation_chat_messages');
    }
};
