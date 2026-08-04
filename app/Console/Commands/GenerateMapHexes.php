<?php

namespace App\Console\Commands;

use App\Models\MapHex;
use App\Services\Maps\HexGridGenerator;
use Illuminate\Console\Command;

class GenerateMapHexes extends Command
{
    protected $signature = 'map:generate-hexes
        {--radius=18 : Hex radius in image pixels}
        {--width=1280 : Map image width in pixels}
        {--height=1280 : Map image height in pixels}
        {--orientation=pointy-top : pointy-top or flat-top}
        {--fresh : Delete existing hexes before generating}';

    protected $description = 'Generate a hexagonal territory grid using image pixel coordinates.';

    public function handle(HexGridGenerator $generator): int
    {
        $orientation = (string) $this->option('orientation');

        if (! in_array($orientation, ['pointy-top', 'flat-top'], true)) {
            $this->error('Orientation must be pointy-top or flat-top.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            MapHex::query()->delete();
        }

        $hexes = $generator->generate(
            (int) $this->option('width'),
            (int) $this->option('height'),
            (float) $this->option('radius'),
            $orientation
        );

        foreach ($hexes as $hex) {
            $mapHex = MapHex::query()->firstOrNew([
                'grid_column' => $hex['grid_column'],
                'grid_row' => $hex['grid_row'],
            ]);

            $mapHex->fill($hex);

            if (! $mapHex->exists) {
                $mapHex->tile_type = MapHex::TYPE_CLAIMABLE;
                $mapHex->is_visible = true;
            }

            $mapHex->save();
        }

        $this->info('Generated '.count($hexes).' map hexes. Coordinates are stored as x/y image pixels; Leaflet consumes them as [y, x].');

        return self::SUCCESS;
    }
}
