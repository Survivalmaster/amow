<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('changelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discord_webhook_id')->nullable()->constrained()->nullOnDelete();
            $table->string('version');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->json('features')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('released_at')->nullable();
            $table->timestamp('discord_message_sent_at')->nullable();
            $table->timestamps();

            $table->unique('version');
            $table->index(['status', 'released_at']);
        });

        Permission::query()
            ->where('slug', 'admin')
            ->get()
            ->each(function (Permission $permission): void {
                $sections = $permission->admin_sections ?? [];

                if (! in_array('changelogs', $sections, true)) {
                    $sections[] = 'changelogs';
                    $permission->forceFill(['admin_sections' => $sections])->save();
                }
            });
    }

    public function down(): void
    {
        Permission::query()
            ->where('slug', 'admin')
            ->get()
            ->each(function (Permission $permission): void {
                $sections = array_values(array_filter(
                    $permission->admin_sections ?? [],
                    fn (string $section): bool => $section !== 'changelogs'
                ));

                $permission->forceFill(['admin_sections' => $sections])->save();
            });

        Schema::dropIfExists('changelogs');
    }
};
