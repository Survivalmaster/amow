<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Licence;
use App\Models\Rank;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $private = Rank::query()->where('name', 'Private')->first();
        $captain = Rank::query()->where('name', 'Captain')->first();
        $businessOwner = Licence::query()->where('slug', 'business-owner')->first();

        $items = [
            ['name' => 'Worker Toolkit', 'slug' => 'worker-toolkit', 'description' => 'Basic tools for mechanics and labour crews.', 'type' => 'utility', 'price' => 80, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => null, 'stock' => null],
            ['name' => 'Merchant Ledger', 'slug' => 'merchant-ledger', 'description' => 'Used to track trade margins and contracts.', 'type' => 'trade', 'price' => 110, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => null, 'stock' => null],
            ['name' => 'Plastic Rifle', 'slug' => 'plastic-rifle', 'description' => 'Standard-issue weapon for frontline troops.', 'type' => 'military', 'price' => 150, 'required_role_type' => 'military', 'required_rank_id' => $private?->id, 'required_licence_id' => null, 'stock' => 250],
            ['name' => 'Officer Sidearm', 'slug' => 'officer-sidearm', 'description' => 'Sidearm reserved for advanced officers.', 'type' => 'military', 'price' => 260, 'required_role_type' => 'military', 'required_rank_id' => $captain?->id, 'required_licence_id' => null, 'stock' => 75],
            ['name' => 'Market Stall Deed', 'slug' => 'market-stall-deed', 'description' => 'Operate a faction-approved commercial stall.', 'type' => 'business', 'price' => 320, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => $businessOwner?->id, 'stock' => 40],
        ];

        foreach ($items as $item) {
            Item::query()->updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
