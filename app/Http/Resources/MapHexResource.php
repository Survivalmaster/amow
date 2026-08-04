<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MapHexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grid_column' => $this->grid_column,
            'grid_row' => $this->grid_row,
            'centre_x' => (float) $this->centre_x,
            'centre_y' => (float) $this->centre_y,
            'polygon_coordinates' => $this->polygon_coordinates,
            'tile_type' => $this->tile_type,
            'terrain_type' => $this->terrain_type,
            'is_visible' => (bool) $this->is_visible,
            'claim_strength' => (int) $this->claim_strength,
            'claimed_at' => $this->claimed_at?->toIso8601String(),
            'faction' => $this->when($this->faction, fn () => [
                'id' => $this->faction->id,
                'name' => $this->faction->name,
                'slug' => $this->faction->slug,
                'colour' => $this->faction->color,
                'color' => $this->faction->color,
                'emblem_path' => $this->faction->flag_image,
            ]),
        ];
    }
}
