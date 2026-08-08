<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterJobProgress extends Model
{
    protected $table = 'character_job_progress';

    protected $fillable = [
        'character_id',
        'game_job_id',
        'tier',
        'tier_experience',
    ];

    protected function casts(): array
    {
        return [
            'tier' => 'integer',
            'tier_experience' => 'integer',
        ];
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(GameJob::class, 'game_job_id');
    }
}
