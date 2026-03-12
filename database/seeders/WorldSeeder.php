<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Faction;
use App\Models\Licence;
use App\Models\Location;
use App\Models\Rank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $major = Rank::query()->where('name', 'Major')->first();
        $recruitId = Rank::query()->where('name', 'Recruit')->value('id');
        $senator = Licence::query()->where('slug', 'senator')->first();
        $mapPoints = [
            'Red Empire' => ['x' => 22, 'y' => 32],
            'Kingdom of Greenland' => ['x' => 38, 'y' => 18],
            'Tan Empire' => ['x' => 61, 'y' => 28],
            'Tangerian Republic' => ['x' => 52, 'y' => 55],
            'Imperial Graul' => ['x' => 74, 'y' => 46],
            'Kingdom of Blutannia' => ['x' => 31, 'y' => 68],
            'Obsidian Purl' => ['x' => 81, 'y' => 67],
            'New Purlanese Republic' => ['x' => 57, 'y' => 80],
        ];

        Faction::query()->get()->each(function (Faction $faction) use ($major, $recruitId, $senator, $mapPoints) {
            $point = $mapPoints[$faction->name] ?? ['x' => 50, 'y' => 50];
            $city = City::query()->updateOrCreate(
                ['slug' => Str::slug($faction->name.' Capital')],
                [
                    'faction_id' => $faction->id,
                    'name' => $faction->name.' Capital',
                    'slug' => Str::slug($faction->name.' Capital'),
                    'description' => "The administrative and military center of {$faction->name}.",
                    'map_x' => $point['x'],
                    'map_y' => $point['y'],
                ]
            );

            $locations = [
                ['name' => 'Royal Palace', 'slug' => 'royal-palace', 'description' => 'Court politics, decrees, and ceremonial authority.', 'required_rank_id' => $major?->id, 'required_licence_id' => $senator?->id, 'is_public' => false],
                ['name' => 'Casino', 'slug' => 'casino', 'description' => 'Risky tables and fast Plastic Credit swings.', 'required_rank_id' => null, 'required_licence_id' => null, 'is_public' => true],
                ['name' => 'Military Academy', 'slug' => 'military-academy', 'description' => 'Training ground for tactical advancement.', 'required_rank_id' => $recruitId, 'required_licence_id' => null, 'is_public' => false],
                ['name' => 'City Streets', 'slug' => 'city-streets', 'description' => 'Public space for open text roleplay and encounters.', 'required_rank_id' => null, 'required_licence_id' => null, 'is_public' => true],
                ['name' => 'Tavern', 'slug' => 'tavern', 'description' => 'A neutral social hub for rumours and deals.', 'required_rank_id' => null, 'required_licence_id' => null, 'is_public' => true],
                ['name' => 'Marketplace', 'slug' => 'marketplace', 'description' => 'Trade goods, negotiate deals, and watch prices move.', 'required_rank_id' => null, 'required_licence_id' => null, 'is_public' => true],
                ['name' => 'Go to Work', 'slug' => 'go-to-work', 'description' => 'Complete a shift for quick income.', 'required_rank_id' => null, 'required_licence_id' => null, 'is_public' => true],
            ];

            foreach ($locations as $location) {
                Location::query()->updateOrCreate(
                    ['city_id' => $city->id, 'slug' => $location['slug']],
                    ['city_id' => $city->id] + $location
                );
            }
        });
    }
}
