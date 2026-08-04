<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->boolean('grants_business_creation')->default(false)->after('required_rank_id');
        });

        DB::table('licences')
            ->where('slug', 'business-owner')
            ->update(['grants_business_creation' => true]);
    }

    public function down(): void
    {
        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('grants_business_creation');
        });
    }
};
