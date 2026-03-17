<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapMarker extends Model
{
    protected $fillable = [
        'name',
        'faction_id',
        'icon_type',
        'icon_class',
        'map_x',
        'map_y',
        'color',
        'description',
    ];

    public function getIconAssetUrlAttribute(): ?string
    {
        if ($this->icon_type !== 'image' || blank($this->icon_class)) {
            return null;
        }

        return asset('images/mapicons/'.$this->icon_class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
