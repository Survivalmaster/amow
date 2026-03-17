<?php

namespace Database\Seeders;

use App\Models\AccountIcon;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountIconSeeder extends Seeder
{
    public function run(): void
    {
        $adminCrown = AccountIcon::query()->updateOrCreate(
            ['slug' => 'admin-crown'],
            [
                'name' => 'Admin Crown',
                'icon_type' => 'fontawesome',
                'icon_value' => 'fa-solid fa-crown',
                'color' => '#e1ba44',
                'tooltip' => 'Administrator',
                'sort_order' => 10,
            ]
        );

        User::query()
            ->where('is_admin', true)
            ->get()
            ->each(fn (User $user) => $user->accountIcons()->syncWithoutDetaching([$adminCrown->id]));
    }
}
