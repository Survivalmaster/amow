<?php

namespace Database\Seeders;

use App\Models\Faction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $factions = [
            ['name' => 'Red Empire', 'short_description' => 'Industrial firepower and rigid command discipline.', 'color' => '#b8443b', 'lore' => 'The Red Empire believes Plastica belongs to whoever can hold the line the longest.'],
            ['name' => 'Kingdom of Greenland', 'short_description' => 'Royal bureaucracy with disciplined civic order.', 'color' => '#5e7c42', 'lore' => 'Greenland blends court politics with stubborn territorial defense.'],
            ['name' => 'Tan Empire', 'short_description' => 'Expansionist machine built on logistics and rank.', 'color' => '#bc8e54', 'lore' => 'The Tan Empire moves methodically, turning roads and depots into leverage.'],
            ['name' => 'Tangerian Republic', 'short_description' => 'Republican traders backed by citizen militias.', 'color' => '#d0722b', 'lore' => 'Tangeria thrives where commerce and local government overlap.'],
            ['name' => 'Imperial Graul', 'short_description' => 'Hard-edged martial society with elite officer culture.', 'color' => '#7f6957', 'lore' => 'Graul rewards battlefield dominance and political loyalty.'],
            ['name' => 'Kingdom of Blutannia', 'short_description' => 'Naval aristocracy with old-money influence.', 'color' => '#496da8', 'lore' => 'Blutannia projects power through prestige, finance, and ceremony.'],
            ['name' => 'Obsidian Purl', 'short_description' => 'Secretive technocrats with a fortified economy.', 'color' => '#4d4f65', 'lore' => 'Obsidian Purl turns scarcity into leverage and information into power.'],
            ['name' => 'New Purlanese Republic', 'short_description' => 'Young republic where reformers and merchants collide.', 'color' => '#4b7d8a', 'lore' => 'The republic promises mobility, but every office is contested.'],
        ];

        foreach ($factions as $faction) {
            Faction::query()->updateOrCreate(
                ['slug' => Str::slug($faction['name'])],
                $faction + ['slug' => Str::slug($faction['name'])]
            );
        }
    }
}
