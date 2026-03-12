<?php

namespace Database\Seeders;

use App\Models\Licence;
use App\Models\Rank;
use Illuminate\Database\Seeder;

class LicenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $captain = Rank::query()->where('name', 'Captain')->first();

        $licences = [
            [
                'name' => 'Senator',
                'slug' => 'senator',
                'description' => 'Access to civic power, debate chambers, and upper-political buildings.',
                'cost' => 400,
                'required_rank_id' => null,
            ],
            [
                'name' => 'Priest',
                'slug' => 'priest',
                'description' => 'Allows service inside faction temples and ceremonial locations.',
                'cost' => 250,
                'required_rank_id' => null,
            ],
            [
                'name' => 'Business Owner',
                'slug' => 'business-owner',
                'description' => 'Unlocks passive daily income and commercial prestige.',
                'cost' => 600,
                'required_rank_id' => $captain?->id,
            ],
        ];

        foreach ($licences as $licence) {
            Licence::query()->updateOrCreate(['slug' => $licence['slug']], $licence);
        }
    }
}
