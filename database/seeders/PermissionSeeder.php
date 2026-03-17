<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full access to the admin area and admin actions.',
                'grants_admin_access' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Developer account badge.',
                'grants_admin_access' => false,
                'sort_order' => 20,
            ],
            [
                'name' => 'Game Master',
                'slug' => 'game-master',
                'description' => 'Game master account badge.',
                'grants_admin_access' => false,
                'sort_order' => 30,
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Moderator account badge.',
                'grants_admin_access' => false,
                'sort_order' => 40,
            ],
        ];

        foreach ($permissions as $attributes) {
            Permission::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes
            );
        }
    }
}
