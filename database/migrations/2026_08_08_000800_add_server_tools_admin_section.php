<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Permission::query()->where('slug', 'admin')->get()->each(function (Permission $permission): void {
            $sections = collect($permission->admin_sections ?? [])
                ->push('server_tools')
                ->unique()
                ->values()
                ->all();

            $permission->forceFill(['admin_sections' => $sections])->save();
        });
    }

    public function down(): void
    {
        Permission::query()->where('slug', 'admin')->get()->each(function (Permission $permission): void {
            $sections = collect($permission->admin_sections ?? [])
                ->reject(fn (string $section): bool => $section === 'server_tools')
                ->values()
                ->all();

            $permission->forceFill(['admin_sections' => $sections])->save();
        });
    }
};
