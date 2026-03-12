<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faction extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'flag_image',
        'lore',
    ];

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function mapMarkers(): HasMany
    {
        return $this->hasMany(MapMarker::class);
    }
}
