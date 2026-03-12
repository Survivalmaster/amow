<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ranks = [
            ['id' => 1, 'name' => 'Civilian', 'order_index' => 1, 'is_military' => false],
            ['id' => 2, 'name' => 'Recruit', 'order_index' => 2, 'is_military' => true],
            ['id' => 3, 'name' => 'Private', 'order_index' => 3, 'is_military' => true],
            ['id' => 4, 'name' => 'Corporal', 'order_index' => 4, 'is_military' => true],
            ['id' => 5, 'name' => 'Sergeant', 'order_index' => 5, 'is_military' => true],
            ['id' => 6, 'name' => 'Lieutenant', 'order_index' => 6, 'is_military' => true],
            ['id' => 7, 'name' => 'Captain', 'order_index' => 7, 'is_military' => true],
            ['id' => 8, 'name' => 'Major', 'order_index' => 8, 'is_military' => true],
            ['id' => 9, 'name' => 'General', 'order_index' => 9, 'is_military' => true],
        ];

        foreach ($ranks as $rank) {
            Rank::query()->updateOrCreate(['id' => $rank['id']], $rank);
        }
    }
}
