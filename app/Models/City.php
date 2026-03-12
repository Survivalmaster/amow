<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'faction_id',
        'name',
        'slug',
        'description',
        'map_x',
        'map_y',
    ];

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}
