<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBusinessMember extends Model
{
    public const STATUS_INVITED = 'invited';
    public const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'player_business_id',
        'character_id',
        'player_business_role_id',
        'invited_by_character_id',
        'status',
        'joined_at',
        'last_paid_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'last_paid_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(PlayerBusiness::class, 'player_business_id');
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(PlayerBusinessRole::class, 'player_business_role_id');
    }
}
