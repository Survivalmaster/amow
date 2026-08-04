<?php

namespace App\Services\Maps;

class HexGridGenerator
{
    /**
     * Database stores points as x/y image pixels. Leaflet CRS.Simple receives them as [y, x].
     */
    public function generate(int $width, int $height, float $radius, string $orientation): array
    {
        return $orientation === 'flat-top'
            ? $this->generateFlatTop($width, $height, $radius)
            : $this->generatePointyTop($width, $height, $radius);
    }

    public function polygon(float $centerX, float $centerY, float $radius, string $orientation): array
    {
        $offset = $orientation === 'flat-top' ? 0 : -30;

        return collect(range(0, 5))->map(function (int $index) use ($centerX, $centerY, $radius, $offset): array {
            $angle = deg2rad(60 * $index + $offset);

            return [
                'x' => round($centerX + ($radius * cos($angle)), 3),
                'y' => round($centerY + ($radius * sin($angle)), 3),
            ];
        })->all();
    }

    private function generatePointyTop(int $width, int $height, float $radius): array
    {
        $hexWidth = sqrt(3) * $radius;
        $verticalSpacing = 1.5 * $radius;
        $hexes = [];
        $row = 0;

        for ($centerY = $radius; $centerY <= $height + $radius; $centerY += $verticalSpacing, $row++) {
            $columnOffset = $row % 2 === 1 ? $hexWidth / 2 : 0;
            $column = 0;

            for ($centerX = $radius + $columnOffset; $centerX <= $width + $radius; $centerX += $hexWidth, $column++) {
                $hex = $this->hexPayload($column, $row, $centerX, $centerY, $radius, 'pointy-top');

                if ($this->polygonFitsBounds($hex['polygon_coordinates'], $width, $height)) {
                    $hexes[] = $hex;
                }
            }
        }

        return $hexes;
    }

    private function generateFlatTop(int $width, int $height, float $radius): array
    {
        $hexHeight = sqrt(3) * $radius;
        $horizontalSpacing = 1.5 * $radius;
        $hexes = [];
        $column = 0;

        for ($centerX = $radius; $centerX <= $width + $radius; $centerX += $horizontalSpacing, $column++) {
            $rowOffset = $column % 2 === 1 ? $hexHeight / 2 : 0;
            $row = 0;

            for ($centerY = $radius + $rowOffset; $centerY <= $height + $radius; $centerY += $hexHeight, $row++) {
                $hex = $this->hexPayload($column, $row, $centerX, $centerY, $radius, 'flat-top');

                if ($this->polygonFitsBounds($hex['polygon_coordinates'], $width, $height)) {
                    $hexes[] = $hex;
                }
            }
        }

        return $hexes;
    }

    private function hexPayload(int $column, int $row, float $centerX, float $centerY, float $radius, string $orientation): array
    {
        return [
            'grid_column' => $column,
            'grid_row' => $row,
            'centre_x' => round($centerX, 3),
            'centre_y' => round($centerY, 3),
            'polygon_coordinates' => $this->polygon($centerX, $centerY, $radius, $orientation),
        ];
    }

    private function polygonFitsBounds(array $polygon, int $width, int $height): bool
    {
        foreach ($polygon as $point) {
            if ($point['x'] < 0 || $point['x'] > $width || $point['y'] < 0 || $point['y'] > $height) {
                return false;
            }
        }

        return true;
    }
}
