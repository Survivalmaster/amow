<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapMarker extends Model
{
    protected $fillable = [
        'name',
        'faction_id',
        'icon_class',
        'map_x',
        'map_y',
        'color',
        'description',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
