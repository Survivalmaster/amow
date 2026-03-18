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
        'color',
        'lore',
        'nation_bank_credits',
    ];

    protected function casts(): array
    {
        return [
            'nation_bank_credits' => 'integer',
        ];
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function requisitions(): HasMany
    {
        return $this->hasMany(NationRequisition::class);
    }

    public function activeEvents(): HasMany
    {
        return $this->hasMany(GameEvent::class);
    }

    public function mapMarkers(): HasMany
    {
        return $this->hasMany(MapMarker::class);
    }
}
