<?php

namespace Database\Seeders;

use App\Models\Faction;
use App\Models\MapHex;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MapTerritorySeeder extends Seeder
{
    public function run(): void
    {
        $factions = collect([
            ['name' => 'Kingdom of Blueton', 'color' => '#3478C5'],
            ['name' => 'Greenmarch Union', 'color' => '#3D8B4E'],
            ['name' => 'Tan Dominion', 'color' => '#B88746'],
            ['name' => 'Coldlands Republic', 'color' => '#A9D6E5'],
        ])->map(fn (array $faction) => Faction::query()->updateOrCreate(
            ['slug' => Str::slug($faction['name'])],
            [
                ...$faction,
                'slug' => Str::slug($faction['name']),
                'short_description' => $faction['name'].' territory claimant.',
            ]
        ))->values();

        if (MapHex::query()->doesntExist()) {
            return;
        }

        MapHex::query()
            ->orderBy('grid_row')
            ->orderBy('grid_column')
            ->limit(24)
            ->get()
            ->each(function (MapHex $hex, int $index) use ($factions): void {
                $faction = $factions[$index % $factions->count()];

                $hex->forceFill([
                    'tile_type' => MapHex::TYPE_CLAIMABLE,
                    'faction_id' => $faction->id,
                    'claim_strength' => 1,
                    'is_visible' => true,
                    'claimed_at' => now(),
                ])->save();
            });
    }
}
