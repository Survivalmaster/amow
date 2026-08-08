<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameJob extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'min_pay',
        'max_pay',
        'required_level',
        'work_cooldown_minutes',
        'stamina_decrease',
        'experience_reward',
        'max_tier',
        'tier_xp_required',
        'tier_pay_bonus_percent',
        'tier_xp_bonus_percent',
        'working_display_message',
        'is_starter',
        'is_active',
        'is_new',
    ];

    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'max_tier' => 'integer',
            'tier_xp_required' => 'integer',
            'tier_pay_bonus_percent' => 'integer',
            'tier_xp_bonus_percent' => 'integer',
        ];
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class, 'current_job_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CharacterJobProgress::class);
    }

    public function drops(): HasMany
    {
        return $this->hasMany(GameJobDrop::class);
    }
}
