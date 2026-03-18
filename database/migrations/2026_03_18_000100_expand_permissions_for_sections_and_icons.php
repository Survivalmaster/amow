<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('icon_type')->nullable()->after('description');
            $table->string('icon_value')->nullable()->after('icon_type');
            $table->string('icon_color', 20)->nullable()->after('icon_value');
            $table->string('icon_tooltip')->nullable()->after('icon_color');
            $table->json('admin_sections')->nullable()->after('grants_admin_access');
        });

        $permissions = [
            'admin' => [
                'icon_value' => 'fa-solid fa-crown',
                'icon_color' => '#e1ba44',
                'icon_tooltip' => 'Administrator',
                'grants_admin_access' => true,
                'admin_sections' => array_keys(config('admin_sections')),
            ],
            'developer' => [
                'icon_value' => 'fa-solid fa-code',
                'icon_color' => '#7ec6ff',
                'icon_tooltip' => 'Developer',
                'grants_admin_access' => true,
                'admin_sections' => ['permissions'],
            ],
            'game-master' => [
                'icon_value' => 'fa-solid fa-dice-d20',
                'icon_color' => '#d7edc7',
                'icon_tooltip' => 'Game Master',
                'grants_admin_access' => true,
                'admin_sections' => ['game_master'],
            ],
            'moderator' => [
                'icon_value' => 'fa-solid fa-gavel',
                'icon_color' => '#f0b29f',
                'icon_tooltip' => 'Moderator',
                'grants_admin_access' => true,
                'admin_sections' => ['moderator'],
            ],
        ];

        foreach ($permissions as $slug => $attributes) {
            DB::table('permissions')
                ->where('slug', $slug)
                ->update([
                    'icon_type' => 'fontawesome',
                    'icon_value' => $attributes['icon_value'],
                    'icon_color' => $attributes['icon_color'],
                    'icon_tooltip' => $attributes['icon_tooltip'],
                    'grants_admin_access' => $attributes['grants_admin_access'],
                    'admin_sections' => json_encode($attributes['admin_sections']),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['icon_type', 'icon_value', 'icon_color', 'icon_tooltip', 'admin_sections']);
        });
    }
};
