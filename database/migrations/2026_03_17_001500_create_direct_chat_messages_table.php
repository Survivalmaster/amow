<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direct_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_character_id')->constrained('characters')->cascadeOnDelete();
            $table->foreignId('recipient_character_id')->constrained('characters')->cascadeOnDelete();
            $table->string('message', 400);
            $table->timestamps();
            $table->index(['sender_character_id', 'recipient_character_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_chat_messages');
    }
};
