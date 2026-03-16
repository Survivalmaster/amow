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
        'is_starter',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_starter' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class, 'current_job_id');
    }
}
