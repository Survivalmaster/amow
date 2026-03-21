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
        $land = Licence::query()->where('slug', 'land')->first();

        $items = [
            ['name' => 'Worker Toolkit', 'slug' => 'worker-toolkit', 'description' => 'Basic tools for mechanics and labour crews.', 'type' => 'utility', 'icon_class' => 'fa-solid fa-screwdriver-wrench', 'inventory_slot_bonus' => 0, 'price' => 80, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => null, 'stock' => null],
            ['name' => 'Merchant Ledger', 'slug' => 'merchant-ledger', 'description' => 'Used to track trade margins and contracts.', 'type' => 'trade', 'icon_class' => 'fa-solid fa-scroll', 'inventory_slot_bonus' => 0, 'price' => 110, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => null, 'stock' => null],
            ['name' => 'Canvas Backpack', 'slug' => 'canvas-backpack', 'description' => 'A stitched carry pack that increases how many item slots your character can manage.', 'type' => 'backpack', 'icon_class' => 'fa-solid fa-backpack', 'inventory_slot_bonus' => 8, 'price' => 180, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => null, 'stock' => null],
            ['name' => 'Salvaged Tent', 'slug' => 'salvaged-tent', 'description' => 'A rough starter tent made from patched sheets and faction scrap. Place it on land to begin construction.', 'type' => 'building', 'icon_class' => 'fa-solid fa-tent', 'inventory_slot_bonus' => 0, 'is_home' => true, 'is_building' => true, 'footprint_width' => 1, 'footprint_height' => 1, 'build_time_minutes' => 15, 'price' => 260, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => $land?->id, 'stock' => null],
            ['name' => 'Plastic Rifle', 'slug' => 'plastic-rifle', 'description' => 'Standard-issue weapon for frontline troops.', 'type' => 'military', 'icon_class' => 'fa-solid fa-gun', 'inventory_slot_bonus' => 0, 'price' => 150, 'required_role_type' => 'military', 'required_rank_id' => $private?->id, 'required_licence_id' => null, 'stock' => 250],
            ['name' => 'Officer Sidearm', 'slug' => 'officer-sidearm', 'description' => 'Sidearm reserved for advanced officers.', 'type' => 'military', 'icon_class' => 'fa-solid fa-gun', 'inventory_slot_bonus' => 0, 'price' => 260, 'required_role_type' => 'military', 'required_rank_id' => $captain?->id, 'required_licence_id' => null, 'stock' => 75],
            ['name' => 'Market Stall Deed', 'slug' => 'market-stall-deed', 'description' => 'Operate a faction-approved commercial stall.', 'type' => 'business', 'icon_class' => 'fa-solid fa-briefcase', 'inventory_slot_bonus' => 0, 'price' => 320, 'required_role_type' => 'civilian', 'required_rank_id' => null, 'required_licence_id' => $businessOwner?->id, 'stock' => 40],
        ];

        foreach ($items as $item) {
            Item::query()->updateOrCreate(['slug' => $item['slug']], $item);
        }
    }
}
