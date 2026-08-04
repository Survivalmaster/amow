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
                'name' => 'Land',
                'slug' => 'land',
                'description' => 'Unlocks a personal 10x10 land plot where buildings can be placed and constructed.',
                'cost' => 260,
                'required_rank_id' => null,
                'grants_business_creation' => false,
            ],
            [
                'name' => 'Senator',
                'slug' => 'senator',
                'description' => 'Access to civic power, debate chambers, and upper-political buildings.',
                'cost' => 400,
                'required_rank_id' => null,
                'grants_business_creation' => false,
            ],
            [
                'name' => 'Priest',
                'slug' => 'priest',
                'description' => 'Allows service inside faction temples and ceremonial locations.',
                'cost' => 250,
                'required_rank_id' => null,
                'grants_business_creation' => false,
            ],
            [
                'name' => 'Business Owner',
                'slug' => 'business-owner',
                'description' => 'Unlocks passive daily income, commercial prestige, and player business creation.',
                'cost' => 600,
                'required_rank_id' => $captain?->id,
                'grants_business_creation' => true,
            ],
        ];

        foreach ($licences as $licence) {
            Licence::query()->updateOrCreate(['slug' => $licence['slug']], $licence);
        }
    }
}
