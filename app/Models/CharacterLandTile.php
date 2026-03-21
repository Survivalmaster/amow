<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterLandTile extends Model
{
    public const STATE_BLOCKED = 'blocked';
    public const STATE_CLEARING = 'clearing';
    public const STATE_OPEN = 'open';

    protected $fillable = [
        'character_id',
        'grid_x',
        'grid_y',
        'state',
        'obstacle_type',
        'clear_started_at',
        'clear_complete_at',
        'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'grid_x' => 'integer',
            'grid_y' => 'integer',
            'clear_started_at' => 'datetime',
            'clear_complete_at' => 'datetime',
            'cleared_at' => 'datetime',
        ];
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function isBlocked(): bool
    {
        return $this->state === self::STATE_BLOCKED;
    }

    public function isClearing(): bool
    {
        return $this->state === self::STATE_CLEARING;
    }

    public function isOpen(): bool
    {
        return $this->state === self::STATE_OPEN;
    }
}
