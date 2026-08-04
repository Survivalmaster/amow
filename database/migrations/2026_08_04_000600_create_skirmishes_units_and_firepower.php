<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->unsignedInteger('firepower_score')->default(0)->after('economic_score');
        });

        Schema::create('skirmishes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->default('infantry');
            $table->unsignedInteger('firepower')->default(0);
            $table->unsignedInteger('cost')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $this->addAdminSections();
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('skirmishes');

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('firepower_score');
        });

        $this->removeAdminSections();
    }

    private function addAdminSections(): void
    {
        $sectionsToAdd = ['skirmishes', 'units'];

        DB::table('permissions')
            ->where('slug', 'admin')
            ->orderBy('id')
            ->get()
            ->each(function ($permission) use ($sectionsToAdd) {
                $sections = json_decode($permission->admin_sections ?: '[]', true) ?: [];

                foreach ($sectionsToAdd as $section) {
                    if (! in_array($section, $sections, true)) {
                        $sections[] = $section;
                    }
                }

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update(['admin_sections' => json_encode(array_values($sections))]);
            });
    }

    private function removeAdminSections(): void
    {
        $sectionsToRemove = ['skirmishes', 'units'];

        DB::table('permissions')
            ->where('slug', 'admin')
            ->orderBy('id')
            ->get()
            ->each(function ($permission) use ($sectionsToRemove) {
                $sections = json_decode($permission->admin_sections ?: '[]', true) ?: [];
                $sections = array_values(array_diff($sections, $sectionsToRemove));

                DB::table('permissions')
                    ->where('id', $permission->id)
                    ->update(['admin_sections' => json_encode($sections)]);
            });
    }
};
