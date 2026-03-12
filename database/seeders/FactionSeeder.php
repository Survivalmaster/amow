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
            ['name' => 'Red Empire', 'short_description' => 'Industrial firepower and rigid command discipline.', 'lore' => 'The Red Empire believes Plastica belongs to whoever can hold the line the longest.'],
            ['name' => 'Kingdom of Greenland', 'short_description' => 'Royal bureaucracy with disciplined civic order.', 'lore' => 'Greenland blends court politics with stubborn territorial defense.'],
            ['name' => 'Tan Empire', 'short_description' => 'Expansionist machine built on logistics and rank.', 'lore' => 'The Tan Empire moves methodically, turning roads and depots into leverage.'],
            ['name' => 'Tangerian Republic', 'short_description' => 'Republican traders backed by citizen militias.', 'lore' => 'Tangeria thrives where commerce and local government overlap.'],
            ['name' => 'Imperial Graul', 'short_description' => 'Hard-edged martial society with elite officer culture.', 'lore' => 'Graul rewards battlefield dominance and political loyalty.'],
            ['name' => 'Kingdom of Blutannia', 'short_description' => 'Naval aristocracy with old-money influence.', 'lore' => 'Blutannia projects power through prestige, finance, and ceremony.'],
            ['name' => 'Obsidian Purl', 'short_description' => 'Secretive technocrats with a fortified economy.', 'lore' => 'Obsidian Purl turns scarcity into leverage and information into power.'],
            ['name' => 'New Purlanese Republic', 'short_description' => 'Young republic where reformers and merchants collide.', 'lore' => 'The republic promises mobility, but every office is contested.'],
        ];

        foreach ($factions as $faction) {
            Faction::query()->updateOrCreate(
                ['slug' => Str::slug($faction['name'])],
                $faction + ['slug' => Str::slug($faction['name'])]
            );
        }
    }
}
