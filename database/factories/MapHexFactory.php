<?php

namespace Database\Factories;

use App\Models\MapHex;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MapHex>
 */
class MapHexFactory extends Factory
{
    protected $model = MapHex::class;

    public function definition(): array
    {
        return [
            'grid_column' => fake()->unique()->numberBetween(0, 1000),
            'grid_row' => fake()->numberBetween(0, 1000),
            'centre_x' => 100,
            'centre_y' => 100,
            'polygon_coordinates' => [
                ['x' => 100, 'y' => 75],
                ['x' => 122, 'y' => 87.5],
                ['x' => 122, 'y' => 112.5],
                ['x' => 100, 'y' => 125],
                ['x' => 78, 'y' => 112.5],
                ['x' => 78, 'y' => 87.5],
            ],
            'tile_type' => MapHex::TYPE_CLAIMABLE,
            'terrain_type' => null,
            'claim_strength' => 0,
            'is_visible' => true,
            'claimed_at' => null,
        ];
    }
}
