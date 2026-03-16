<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapPolygon extends Model
{
    protected $fillable = [
        'name',
        'faction_id',
        'stroke_color',
        'fill_color',
        'fill_opacity',
        'stroke_weight',
        'coordinates',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'coordinates' => 'array',
            'fill_opacity' => 'float',
        ];
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
