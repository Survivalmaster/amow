<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBusinessLog extends Model
{
    protected $fillable = [
        'player_business_id',
        'actor_character_id',
        'type',
        'amount',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(PlayerBusiness::class, 'player_business_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'actor_character_id');
    }
}
