<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameEvent extends Model
{
    protected $fillable = [
        'faction_id',
        'created_by_user_id',
        'title',
        'body',
        'is_enabled',
        'xp_multiplier_enabled',
        'xp_multiplier',
        'credit_multiplier_enabled',
        'credit_multiplier',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'xp_multiplier_enabled' => 'boolean',
            'xp_multiplier' => 'float',
            'credit_multiplier_enabled' => 'boolean',
            'credit_multiplier' => 'float',
        ];
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
