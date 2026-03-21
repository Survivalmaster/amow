<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterLandBuilding extends Model
{
    protected $fillable = [
        'character_id',
        'item_id',
        'grid_x',
        'grid_y',
        'build_started_at',
        'build_complete_at',
    ];

    protected function casts(): array
    {
        return [
            'grid_x' => 'integer',
            'grid_y' => 'integer',
            'build_started_at' => 'datetime',
            'build_complete_at' => 'datetime',
        ];
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function isComplete(): bool
    {
        return $this->build_complete_at !== null && $this->build_complete_at->lte(now());
    }
}
