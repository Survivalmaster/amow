<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'admin@amow.local'],
            [
                'name' => 'AMOW Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $adminPermissionId = Permission::query()->where('slug', 'admin')->value('id');

        if ($adminPermissionId) {
            $user->permissions()->syncWithoutDetaching([$adminPermissionId]);
        }
    }
}
