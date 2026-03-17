<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'Plastica Railworks', 'slug' => 'plastica-railworks', 'current_price' => 42.50, 'last_price_updated_at' => now(), 'description' => 'Critical logistics and freight corridors across Plastica.'],
            ['name' => 'Toyforge Armaments', 'slug' => 'toyforge-armaments', 'current_price' => 66.00, 'last_price_updated_at' => now(), 'description' => 'Military plastics, molds, and officer equipment contracts.'],
            ['name' => 'Greenline Mercantile', 'slug' => 'greenline-mercantile', 'current_price' => 31.75, 'last_price_updated_at' => now(), 'description' => 'Retail trading syndicate embedded in faction marketplaces.'],
            ['name' => 'Crown Resin Energy', 'slug' => 'crown-resin-energy', 'current_price' => 54.20, 'last_price_updated_at' => now(), 'description' => 'Industrial resin fuel and strategic manufacturing inputs.'],
        ];

        foreach ($companies as $company) {
            Company::query()->updateOrCreate(['slug' => $company['slug']], $company);
        }
    }
}
