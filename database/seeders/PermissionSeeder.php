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
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-crown',
                'icon_color' => '#e1ba44',
                'icon_tooltip' => 'Administrator',
                'grants_admin_access' => true,
                'admin_sections' => array_keys(config('admin_sections')),
                'sort_order' => 10,
            ],
            [
                'name' => 'Developer',
                'slug' => 'developer',
                'description' => 'Developer account badge.',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-code',
                'icon_color' => '#7ec6ff',
                'icon_tooltip' => 'Developer',
                'grants_admin_access' => true,
                'admin_sections' => ['permissions'],
                'sort_order' => 20,
            ],
            [
                'name' => 'Game Master',
                'slug' => 'game-master',
                'description' => 'Game master account badge.',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-dice-d20',
                'icon_color' => '#d7edc7',
                'icon_tooltip' => 'Game Master',
                'grants_admin_access' => true,
                'admin_sections' => ['game_master'],
                'sort_order' => 30,
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Moderator account badge.',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-gavel',
                'icon_color' => '#f0b29f',
                'icon_tooltip' => 'Moderator',
                'grants_admin_access' => true,
                'admin_sections' => ['moderator'],
                'sort_order' => 40,
            ],
            [
                'name' => 'Nation Leader',
                'slug' => 'nation-leader',
                'description' => 'Nation leadership badge and requisition access.',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-star',
                'icon_color' => '#f4d77a',
                'icon_tooltip' => 'Nation Leader',
                'grants_admin_access' => false,
                'admin_sections' => [],
                'sort_order' => 50,
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
