<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->foreignId('current_job_id')->nullable()->after('starting_occupation')->constrained('game_jobs')->nullOnDelete();
            $table->unsignedInteger('level')->default(0)->after('economic_score');
            $table->unsignedInteger('experience_points')->default(0)->after('level');
            $table->timestamp('job_changed_at')->nullable()->after('last_worked_at');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_job_id');
            $table->dropColumn(['level', 'experience_points', 'job_changed_at']);
        });
    }
};
