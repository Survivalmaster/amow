<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Licence extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'cost',
        'required_rank_id',
    ];

    public function requiredRank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'required_rank_id');
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_licences')->withTimestamps();
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class, 'required_licence_id');
    }
}
