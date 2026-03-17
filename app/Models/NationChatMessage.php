<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NationChatMessage extends Model
{
    protected $fillable = [
        'character_id',
        'faction_id',
        'message',
    ];

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }
}
